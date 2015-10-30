<?php

namespace EightyEight\SkyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
    private $doctrine;
    private $manager;
    private $rootDir;
    private $directory;
    private $files = array(
        'lines' => array(
            'name' => "const_lines.csv",
            'size' => 0
        ),
        'names' => array(
            'name' => "cont_names.csv",
            'size' => 0
        ),
        'stars' => array(
            'name' => "yale.csv",
            'size' => 0
        )
    );

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
        $output->writeln("<info>[{$this->time()}][INFO] Start</info>");

        // Récupération et configuration des objets de Doctrine
        $this->doctrine = $this->getContainer()->get('doctrine');
        $this->doctrine->getConnection()->getConfiguration()->setSQLLogger(null); // On ne veut pas de logs
        $this->manager = $this->doctrine->getManager();

        // Import des données
        $this->import($input, $output);

        // Fin du script
        $end = new \DateTime();
        $duration = $start->diff($end);
        $output->writeln("<info>[{$this->time()}][INFO] Script ended with code $this->exitCode (execution time: {$duration->format('%s')} seconds)</info>");

        return $this->exitCode;
    }

    private function import(InputInterface $input, OutputInterface $output)
    {
        // On verifie si tous les fichiers existent
        if (!$this->files_exists($input, $output)) {
            $this->exitCode = 1;
            $output->writeln("<error>[{$this->time()}][ERROR] Couldn't find all required files at '$this->directory' !</error>");
            $output->writeln("<error>[{$this->time()}][ERROR] Required files are :</error>");

            foreach ($this->files as $file) {
                $filename = $file['name'];
                $output->writeln("<error>[{$this->time()}][ERROR] - $filename</error>");
            }

            return false;
        }
    }

    private function files_exists(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->files as $file) {
            $filename = $file['name'];
            $path = "$this->directory/$filename";

            if (is_file($path)) {
                $size = count(file($path));

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