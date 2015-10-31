<?php

namespace EightyEight\SkyBundle\Command;

use EightyEight\SkyBundle\Entity\Constellation;
use EightyEight\SkyBundle\Entity\Line;
use EightyEight\SkyBundle\Entity\Star;
use EightyEight\SkyBundle\Services\AstroMaths;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Alexis
 * Date: 30/10/2015
 * Time: 22:36
 */
class LoadDataCommand extends ContainerAwareCommand
{
    private $exitCode = 0;

    private $doctrine = null;
    private $manager = null;
    private $rootDir = "";
    private $directory = "";
    private $files = array(
        'lines' => array(
            'name' => "const_lines.csv",
            'size' => 0
        ),
        'constellations' => array(
            'name' => "const_names.csv",
            'size' => 0
        ),
        'stars' => array(
            'name' => "yale.csv",
            'size' => 0
        )
    );

    private $constellations = array();
    private $stars = array();
    private $lines = array();

    protected function configure()
    {
        $this
            ->setName('sky:load:data')
            ->setDescription("Charge les données dans la base de données.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $this->directory = realpath("$this->rootDir/../web/data");
        $start = new \DateTime();

        $output->writeln(
            '<comment>Executing LoadDataCommand.php, will import CSV files for sky into database</comment>'
        );
        $output->writeln("<comment>[{$this->time()}][INFO] Start</comment>");

        // Récupération et configuration des objets de Doctrine
        $this->doctrine = $this->getContainer()->get('doctrine');
        $this->doctrine->getConnection()->getConfiguration()->setSQLLogger(null); // On ne veut pas de logs
        $this->manager = $this->doctrine->getManager();

        // Import des données
        $this->import($input, $output);

        // Fin du script
        $end = new \DateTime();
        $duration = $start->diff($end);
        $output->writeln(
            "<info>[{$this->time()}][INFO] Script ended with code $this->exitCode (execution time: {$duration->format(
                '%s'
            )} seconds)</info>"
        );

        return $this->exitCode;
    }

    private function import(InputInterface $input, OutputInterface $output)
    {
        // On verifie si tous les fichiers existent
        if (!$this->files_exists($input, $output)) {
            $output->writeln(
                "<error>[{$this->time()}][ERROR] Couldn't find all required files at '$this->directory' !</error>"
            );
            $output->writeln("<error>[{$this->time()}][ERROR] Required files are :</error>");
            foreach ($this->files as $file) {
                $filename = $file['name'];
                $output->writeln("<error>[{$this->time()}][ERROR] - $filename</error>");
            }

            $this->exitCode = 1;

            return;
        }

        // Suppression des donnees
        $this->drop($input, $output);

        // Import des donnees
        $output->writeln("<comment>[{$this->time()}][INFO] Starting to import data</comment>");
        $this->importConstellations($input, $output);
        $this->importStars($input, $output);
        $this->importLines($input, $output);
    }

    private function importConstellations(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<comment>[{$this->time()}][INFO] Importing constellations</comment>");

        $size = $this->files['constellations']['size'];
        $filename = $this->files['constellations']['name'];

        // Définition de la progress bar
        $progress = new ProgressBar($output, $size);
        $progress->start();
        $i = 0;

        // Ouverture du fichier
        $handle = fopen("$this->directory/$filename", 'r');

        // Parcours des lignes
        while (($row = fgetcsv($handle, null, ';'))) {
            $progress->advance(1);

            // Extraction des variables
            list($name, $latin, , $code, $rightAscension, $declination, $zodiac, $hemisphere, $source) = $row;

            // Corrections
            $rightAscension = AstroMaths::hms2hours($rightAscension);
            $declination = AstroMaths::dms2deg($declination);

            // Instanciation de l'entité
            $constellation = new Constellation();
            $constellation->setName($name);
            $constellation->setLatin($latin);
            $constellation->setCode($code);
            $constellation->setRightAscension($rightAscension);
            $constellation->setDeclination($declination);
            $constellation->setZodiac($zodiac);
            $constellation->setHemisphere($hemisphere);
            $constellation->setSource($source);

            // Sauvegarde et persistence
            $this->constellations[strtolower($code)] = $constellation;
            $this->manager->persist($constellation);

            $i++;
        }

        // Fin de parcours
        $progress->finish();
        $output->writeln('');

        $output->writeln("<info>[{$this->time()}][INFO] Data successfuly loaded !</info>");
        $output->writeln("<info>[{$this->time()}][INFO] $i lines were loaded</info>");
        $output->writeln("<info>[{$this->time()}][INFO] " . ($size - $i) . " lines were ignored</info>");

        $output->writeln("<comment>[{$this->time()}][INFO] Flushing data (may take a while)</comment>");

        // Enregistrement en BDD
        $this->manager->flush();
        $this->manager->clear();

        $output->writeln("<info>[{$this->time()}][INFO] Data successfuly flushed into database !</info>");
        $output->writeln("<comment>[{$this->time()}][INFO] Done importing constellations</comment>");
    }

    private function importStars(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<comment>[{$this->time()}][INFO] Importing stars</comment>");

        $size = $this->files['stars']['size'];
        $filename = $this->files['stars']['name'];

        // Définition de la progress bar
        $progress = new ProgressBar($output, $size);
        $progress->start();
        $i = 0;

        // Ouverture du fichier
        $handle = fopen("$this->directory/$filename", 'r');

        // Parcours des lignes
        while (($row = fgetcsv($handle, null, ';'))) {
            $progress->advance(1);

            // Extraction des variables
            list($h, $m, $s, $arc_h, $arc_m, $magnitude, $type, $spectrum, $bayer, $flamsteed, $code, $name) = $row;

            // Corrections
            $rightAscension = (float)number_format(AstroMaths::hms2hours("$h $m $s"), 6);
            $declination = (float)number_format(AstroMaths::dms2deg("$arc_h $arc_m"), 4);
            $constallation = isset($this->constellations[strtolower($code)]) ? $this->constellations[strtolower($code)] : null;

            // Attention à Sirus qui à une magnitude de -99 pour des raisons de format (3bits)
            // la vrai magnitude est ainsi -147
            // On divise par 100 pour obtenir la magnitude réelle
            $magnitude = ($magnitude == -99) ? -147 / 100 : $magnitude / 100;

            // Instanciation de l'entité
            $star = new Star();
            $star->setRightAscension($rightAscension);
            $star->setDeclination($declination);
            $star->setMagnitute($magnitude);
            $star->setBayer($bayer);
            $star->setType($type);
            $star->setSpectrum($spectrum);
            $star->setName($name);
            $star->setConstellation($constallation);

            // Sauvegarde et persistence
            $this->stars[] = $star;
            $this->manager->persist($star);

            $i++;
        }

        // Fin de parcours
        $progress->finish();
        $output->writeln('');

        $output->writeln("<info>[{$this->time()}][INFO] Data successfuly loaded !</info>");
        $output->writeln("<info>[{$this->time()}][INFO] $i lines were loaded</info>");
        $output->writeln("<info>[{$this->time()}][INFO] " . ($size - $i) . " lines were ignored</info>");

        $output->writeln("<comment>[{$this->time()}][INFO] Flushing data (may take a while)</comment>");

        // Enregistrement en BDD
        $this->manager->flush();
        $this->manager->clear();

        $output->writeln("<info>[{$this->time()}][INFO] Data successfuly flushed into database !</info>");
        $output->writeln("<comment>[{$this->time()}][INFO] Done importing stars</comment>");
    }

    private function importLines(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<comment>[{$this->time()}][INFO] Importing lines</comment>");

        $size = $this->files['lines']['size'];
        $filename = $this->files['lines']['name'];

        // Définition de la progress bar
        $progress = new ProgressBar($output, $size);
        $progress->start();
        $i = 0;
        $current_const = null;
        $previous_coords = array();
        $current_coords = array();

        // Ouverture du fichier
        $handle = fopen("$this->directory/$filename", 'r');

        // Parcours des lignes
        while (($row = fgetcsv($handle, null, ';'))) {
            $progress->advance(1);

            // 1ère colonne = code de la constellation
            $const_code = array_shift($row);

            // Correctif, si jamais il lit des lignes vierges en trop ...
            if ($const_code == ""){
                continue;
            }

            $constellation = $this->constellations[strtolower($const_code)];

            // Si le code est différent du courant, on commence une nouvelle constellation
            if ($current_const == null || $constellation->getCode() != $current_const->getCode()) {
                $current_const = $constellation;
                $previous_coords = array();
            }

            // Extraction des variables
            list(, $right_ascension, $declination) = $row;

            // On doit avoir les coordonnées, sinon next
            if ($right_ascension == "" || $declination == "") {
                $previous_coords = array();
                continue;
            }

            // Uniformisation des coordonnées
            $ra_2 = (float)str_replace(',', '.', $right_ascension);
            $dec_2 = (float)str_replace(',', '.', $declination);

            $current_coords = array($ra_2, $dec_2);

            // Si on a pas de précédant, on est sur le début du tracé
            // on met alors le point courant comme prédécesseur, et on boucle
            if(empty($previous_coords)) {
                $previous_coords = $current_coords;
                continue;
            }

            // On extrait les coordonnées du point en mémoire
            list($ra_1, $dec_1) = $previous_coords;

            $star1 = $this->manager->getRepository('EightyEightSkyBundle:Star')->closestFrom($ra_1, $dec_1, $constellation->getCode());
            $star2 = $this->manager->getRepository('EightyEightSkyBundle:Star')->closestFrom($ra_2, $dec_2, $constellation->getCode());

            $line = new Line();
            $line->setStart($star1);
            $line->setEnd($star2);
            $line->setConstellation($constellation);

            $this->manager->persist($line);

            $i++;
        }

        // Fin de parcours
        $progress->finish();
        $output->writeln('');

        $output->writeln("<info>[{$this->time()}][INFO] Data successfuly loaded !</info>");
        $output->writeln("<info>[{$this->time()}][INFO] $i lines were loaded</info>");
        $output->writeln("<info>[{$this->time()}][INFO] " . ($size - $i) . " lines were ignored</info>");

        $output->writeln("<comment>[{$this->time()}][INFO] Flushing data (may take a while)</comment>");

        // Enregistrement en BDD
        $this->manager->flush();
        $this->manager->clear();

        $output->writeln("<info>[{$this->time()}][INFO] Data successfuly flushed into database !</info>");
        $output->writeln("<comment>[{$this->time()}][INFO] Done importing lines</comment>");
    }

    private function drop(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<comment>[{$this->time()}][INFO] Emptying schema</comment>");
        $this->manager->getRepository('EightyEightSkyBundle:Constellation')->delete();
        $this->manager->getRepository('EightyEightSkyBundle:Star')->delete();
        $output->writeln("<info>[{$this->time()}][INFO] Schema empty</info>");
    }

    private function files_exists(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->files as $k => $file) {
            $filename = $file['name'];
            $path = "$this->directory/$filename";

            if (is_file($path)) {
                // On retient le nombre de lignes du fichier
                $size = count(file($path));
                $this->files[$k]['size'] = $size;

                $output->writeln("<info>[{$this->time()}][INFO] $filename found at '$this->directory'</info>");
                $output->writeln("<info>[{$this->time()}][INFO] $size lines in $filename</info>");
            } else {
                $output->writeln("<error>[{$this->time()}][ERROR] $filename not found at '$this->directory' !</error>");

                return false;
            }
        }

        return true;
    }

    private function time()
    {
        return (new \DateTime())->format('Y/m/d H:i:s');
    }
}