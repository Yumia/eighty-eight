<?php
/**
 * Created by PhpStorm.
 * User: Alexis
 * Date: 31/10/2015
 * Time: 01:32
 */

namespace EightyEight\SkyBundle\Services;

use DateTime;
use DateTimeZone;

class AstroMaths {
    // PI
    const PI = 3.14159265358979323846;
    const PI_RAD = 0.0174532925199;

    // Obliquité de l'ecliptique
    const ECLIPTIC_OBLIQUITY_DEG = 23.4392911;
    const EOD = self::ECLIPTIC_OBLIQUITY_DEG;

    const DEGREES_IN_HOUR = 15;
    const DEG_IN_H = self::DEGREES_IN_HOUR;

    // Nombre de minutes dans 1 heure
    const MINUTES_IN_HOUR = 60;
    const M_IN_H = self::MINUTES_IN_HOUR;

    // Nombre de secondes dans 1 heure
    const SECONDS_IN_HOUR = 3600.0;
    const S_IN_H = self::SECONDS_IN_HOUR;

    // Date limite du calendrier Julien
    const JULIAN_YEAR = 1582;
    const JYEAR = self::JULIAN_YEAR;
    const JULIAN_MONTH = 10;
    const JMONTH = self::JULIAN_MONTH;
    const JULIAN_DAY = 4;
    const JDAY = self::JULIAN_DAY;

    // Nombre de jours dans 1 siècle Julien
    const DAYS_IN_JULIAN_CENTURY = 36525.0;
    const D_IN_JCENT = self::DAYS_IN_JULIAN_CENTURY;

    // Nombre de jours dans 1 siècle Julien
    const DAYS_IN_JMONTH = 30.6001;
    const D_IN_JM = self::DAYS_IN_JMONTH;

    // Nombre de jours dans 1 siècle Julien
    const DAYS_IN_JYEAR = 365.25;
    const D_IN_JY = self::DAYS_IN_JYEAR;

    // JD référence de l'année 2000
    const J2000 = 2451545.0;

    /**
     * Consinus en degrés
     * @param float $x Angle en degrés
     * @return float
     */
    public static function dcos($x)
    {
        return cos($x * self::PI_RAD);
    }

    /**
     * Sinus en degrés
     * @param float $x Angle en degrés
     * @return float
     */
    public static function dsin($x)
    {
        return sin($x * self::PI_RAD);
    }

    /**
     * Tangante en degrés
     * @param float $x Angle en degrés
     * @return float
     */
    public static function dtan($x)
    {
        return tan($x * self::PI_RAD);
    }

    /**
     * Conversion de degrés, en radians
     * @param float $angle_d Angle en degrés
     * @return float
     */
    function deg2rad($angle_d)
    {
        return (M_PI * $angle_d) / 180;
    }

    /**
     * Conversion de radians, en degrés
     * @param float $angle_r Angle en radians
     * @return float
     */
    function rad2deg($angle_r)
    {
        return (180 * $angle_r) / M_PI;
    }

    public static function normalize_latitude($lat)
    {
        while ($lat < -90 || $lat > 90) {
            $lat = ($lat < -90) ? $lat + 180 : $lat - 180;
        }

        return (float)$lat;
    }

    public static function normalize_longitude($lng)
    {
        while ($lng < -180 || $lng > 180) {
            $lng = ($lng < -180) ? $lng + 360 : $lng - 360;
        }

        return (float)$lng;
    }

    public static function normalize_azimut($lng, $inc = false)
    {
        return self::normalize_degrees($lng, $inc);
    }

    public static function normalize_degrees($deg, $inc = false)
    {
        while ($deg < 0 || ($inc && $deg > 360) || (!$inc && $deg >= 360)) {
            $deg = ($deg < 0) ? $deg + 360 : $deg - 360;
        }

        return (float)$deg;
    }

    public static function belong_to_range($v, $a, $b, $min = null, $max = null)
    {
        if ($a < $b) {
            return ($a <= $v) && ($v <= $b);
        } elseif ($a > $b) {
            if (is_null($max) || is_null($min)) {
                return false;
            }

            return ((($a <= $v) && ($v <= $max)) || (($min <= $v) && ($v <= $b)));
        } else { // $a == $b
            if (is_null($max) || is_null($min)) {
                return false;
            }

            return ($min <= $v) && ($v <= $max);
        }
    }

    /**
     * Permet d'arrondir les valeurs proches d'un entier
     * @param float $n Nombre flotant
     * @param int $d Nombre de décimales
     * @return float
     */
    public static function precision($n, $d = 4)
    {
        return (float)number_format($n, $d);
        //return (number_format($n, $d) == $n) ? round($n) : $n;
    }

    /**
     * Conversion d'une chaîne d'angle en degrés, en degrés décimales
     * @param string $dms Sous la forme : (-)45° 32' 27.36"
     * @return float Angle avec décimales
     */
    public static function dms2deg($dms)
    {
        $arr = array_map('floatval', explode(' ', $dms));
        $sign = substr($dms, 0, 1) != "-"; // Le signe est extrait au début de la chaîne

        $d = isset($arr[0]) ? $arr[0] : 0;
        $m = isset($arr[1]) ? $arr[1] : 0;
        $s = isset($arr[2]) ? $arr[2] : 0;

        if (!$sign) {
            $m *= -1;
            $s *= -1;
        }

        return $d + self::min2hours($m) + self::sec2hours($s);
    }

    public static function deg2dms($deg)
    {
        $arch = floor($deg);

        $deg -= $arch;
        $deg *= 60;

        $arcm = floor($deg);

        $deg -= $arcm;
        $deg *= 60;

        $arcs = $deg;

        return "{$arch}° {$arcm}' {$arcs}\"";
    }

    /**
     * Conversion d'heures, en jours décimales
     * @param $hours
     * @return float
     */
    public static function hours2days($hours)
    {
        return $hours / 24;
    }

    /**
     * Conversion d'heures décimales, en degrés
     * @param float $hours Sous la forme "12.3684"
     * @return float
     */
    public static function hours2deg($hours)
    {
        return $hours * self::DEG_IN_H;
    }

    /**
     * Conversion de degrés, en heures décimales
     * @param float $degrees Sous la forme "94.3645"
     * @return float
     */
    public static function deg2hours($degrees)
    {
        return $degrees / self::DEG_IN_H;
    }

    /**
     * Conversion de minutes, en heures décimales
     * @param float $minutes Nombre de minutes
     * @return float
     */
    public static function min2hours($minutes)
    {
        return $minutes / self::M_IN_H;
    }

    /**
     * Conversion de secondes, en heures décimales
     * @param float $seconds Nombre de secondes
     * @return float
     */
    public static function sec2hours($seconds)
    {
        return $seconds / self::S_IN_H;
    }

    /**
     * Conversion d'une chaîne de temps, en heures décimales
     * @param string $hms Sous la forme : 12h 24m 54.84s
     * @return float Heure avec décimales
     */
    public static function hms2hours($hms)
    {
        $arr = array_map('floatval', explode(' ', $hms));

        $h = isset($arr[0]) ? $arr[0] : 0;
        $m = isset($arr[1]) ? $arr[1] : 0;
        $s = isset($arr[2]) ? $arr[2] : 0;

        return $h + self::min2hours($m) + self::sec2hours($s);
    }

    public static function hours2hms($hours)
    {
        $h = floor($hours);

        $hours -= $h;
        $hours *= 60;

        $m = floor($hours);

        $hours -= $m;
        $hours *= 60;

        $s = $hours;

        return "{$h}h {$m}m {$s}s";
    }

    /**
     * Détermine si une date appartient au calendrier Julien
     * @param int $year
     * @param int $month
     * @param float $day
     * @return bool
     */
    public static function is_julian_date($year, $month = 1, $day = 1.0)
    {
        if (($year < self::JYEAR) || (($year == self::JYEAR && $month < self::JMONTH)) || ($year == self::JYEAR && $month == self::JMONTH && $day <= self::JDAY)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Convertit une date, en nombre de jours du calendrier Julien
     * ref > http://quasar.as.utexas.edu/BillInfo/JulianDatesG.html
     * @param int $year
     * @param int $month
     * @param float $day
     * @return float
     */
    public static function utc_to_JD($year, $month = 1, $day = 1.0)
    {
        $is_julian = self::is_julian_date($year, $month, $day);

        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }

        $A = intval($year / 100);
        $B = ($is_julian) ? 0 : 2 - $A + intval($A / 4);
        $JD = intval(self::D_IN_JY * ($year + 4716)) + intval(self::D_IN_JM * ($month + 1)) + $day + $B - 1524.5;

        return $JD;
    }

    /**
     * Convertit un nombre de jours du calendrier Julien, en date
     * @param float $JD
     * @return array
     */
    public static function JD_to_utc($JD)
    {
        $_JD = $JD + 0.5;
        $arr = explode('.', $_JD);

        $Z = intval($_JD);
        $F = isset($arr[1]) ? floatval("0." . $arr[1]) : 0.0;

        if ($Z < 2299161) {
            $A = $Z;
        } else {
            $alpha = intval(($Z - 1867216.25) / self::D_IN_JCENT);
            $A = $Z + 1 + $alpha - intval($alpha / 4);
        }

        $B = $A + 1524;
        $C = intval(($B - 122.1) / self::D_IN_JY);
        $D = intval(self::D_IN_JY * $C);
        $E = intval(($B - $D) / self::D_IN_JM);

        $day = $B - $D - intval(self::D_IN_JM * $E) + $F;
        $month = ($E < 14) ? $E - 1 : $E - 13;
        $year = ($month > 2) ? $C - 4716 : $C - 4715;

        return [$year, $month, $day];
    }

    /**
     * Angle Horaire (HA)
     * Si $ST est un temps GMST, il est nécessaire
     * de préciser le 3ème paramètre $lng
     *
     * @param float $ST Temps Sidéral, GMST ou LST
     * @param float $ra Ascension droite
     * @param float $lng Longitude
     * @return mixed
     */
    public static function HA($ST, $ra, $lng = null)
    {
        $HA = $ST - self::hours2deg($ra);
        $HA -= ($lng !== null) ? $lng : 0;
        $HA = self::normalize_degrees($HA); // Réduction de la valeur à [0, 360]

        return $HA;
    }

    /**
     * Local Mean Sideral Time
     * @param float $lng Longitude en degrés
     * @param float $JD
     * @return float Temps sidéral en degrés
     */
    public static function LMST($lng = 0.0, $JD = null)
    {
        $JD = ($JD === null) ? self::JD() : $JD;

        //$lng += self::hours2deg(self::min2hours(1.00273790935));
        return self::GMST($JD) + $lng;
    }

    /**
     * Greenwich Apparent Sideral Time
     * @param null $JD
     * @param bool $cut
     * @return float
     */
    public static function GAST($JD = null, $cut = false)
    {
        return self::GMST($JD, $cut, true);
    }

    /**
     * Greenwich Mean Sideral Time
     * Calcul de l'heure sidérale au méridien de Greenwich pour un temps UT donné
     * Afin d'obtenir l'heure sidérale UT0, mettre le paramètre $cut à TRUE
     * L'heure apparente, GAST, est obtenue en passant le paramètre $GAST à TRUE
     *
     * Formules > http://www2.arnes.si/~gljsentvid10/sidereal.htm
     * & Astronomical Algorithms - Chapter 12 Sideral Time at Greenwich
     *
     * @param null $JD Temps UT donné sous forme de Jours Julien
     * @param bool $cut Si le temps UT n'est pas précisé, est-ce qu'on souhaite le UT0 ?
     * @param bool $GAST Si passé à TRUE, le temps sidéral sera corrigé en GAST
     * @return float Heure sidérale en degrés
     */
    public static function GMST($JD = null, $cut = false, $GAST = false)
    {
        $JD = ($JD === null) ? self::JD($cut) : $JD;

        $T = self::JCentury($JD);

        $a = 280.46061837;
        $b = 360.98564736629 * self::JD_from_J2000($JD);
        $c = 0.000387933 * pow($T, 2);
        $d = pow($T, 3) / 38710000;

        $GST = $a + $b + $c - $d;

        // Est-ce que l'on souhaite l'heure apparente ?
        if ($GAST === true) {
            $nt_lng = self::nutation_longitude($JD);
            $obl = self::obliquity($JD);

            $correction = $nt_lng * self::dcos($obl);

            $GST += $correction;
        }

        // Réduction de la valeur à [0, 360]
        $GST = self::normalize_degrees($GST);

        return $GST;
    }

    public static function JCentury($JD)
    {
        $JD = ($JD === null) ? self::JD() : $JD;

        return self::JD_from_J2000($JD) / self::D_IN_JCENT;
    }

    /**
     * Retourne le Julian Day courant, cut permet de ne prendre que le jour
     * @param bool $cut
     * @return float
     */
    public static function JD($cut = false)
    {
        $timezoneGMT = new DateTimeZone("GMT");
        $dateUTCGMT = new DateTime('now', $timezoneGMT);
        $date = $dateUTCGMT;
        $format = $date->format('Y:m:d:H:i:s');
        list($year, $month, $day, $hours, $minutes, $secondes) = explode(':', $format);

        if ($cut === false) {
            $d = self::hms2hours("{$hours}h {$minutes}m {$secondes}s");
            $day += self::hours2days($d);
        }

        $JD = self::utc_to_JD($year, $month, $day);

        return $JD;
    }

    /**
     * Nombre de jours écoulés depuis le JD 2000
     * @param float $JD
     * @return float
     */
    public static function JD_from_J2000($JD)
    {
        return $JD - self::J2000;
    }

    /**
     * @param null $JD
     * @param bool $apparent
     * @return float
     */
    public static function obliquity($JD = null, $apparent = false)
    {
        $JD = ($JD === null) ? self::JD() : $JD;

        $T = self::JCentury($JD);
        $U = $T / 100;

        $a = self::dms2deg("23 26 21.248");
        $b = self::dms2deg("00 00 4680.93");

        $obliquity = $a - ($b * $U);
        $obliquity -= (1.55 * pow($U, 2));
        $obliquity += (1999.25 * pow($U, 3));
        $obliquity -= (51.38 * pow($U, 4));
        $obliquity -= (249.67 * pow($U, 5));
        $obliquity -= (39.05 * pow($U, 6));
        $obliquity += (7.12 * pow($U, 7));
        $obliquity += (27.87 * pow($U, 8));
        $obliquity += (5.79 * pow($U, 9));
        $obliquity += (2.45 * pow($U, 10));

        if ($apparent === true) {
            $obliquity -= self::nutation_obliquity($JD);
        }

        return $obliquity;
    }

    /**
     * Conversion de coordonnées équatoriales (RA, DEC), en coordonnées écliptiques (LNG, LAT)
     * Formules > Astronomical Algorithms - Chapter 13 Transformation of Coordinates
     * @param float $ra_h Ascension droite en heures
     * @param float $dec_d Déclinaison en degrés
     * @return array
     */
    public static function equatorial2ecliptical($ra_h, $dec_d)
    {
        $ra = deg2rad(self::hours2deg($ra_h));
        $dec = deg2rad($dec_d);

        $y = (self::dsin($ra) * self::dcos(self::EOD)) + (self::dtan($dec) * self::dsin(self::EOD));
        $x = self::dcos($ra);
        $sinB = (self::dsin($dec) * self::dcos(self::EOD)) - (self::dcos($dec) * self::dsin(self::EOD) * self::dsin($ra));

        $lng = rad2deg(atan2($y, $x));
        $lat = rad2deg(asin($sinB));

        return [$lng, $lat];
    }

    /**
     * Conversion de coordonnées écliptiques (LNG, LAT), en coordonnées équatoriales (RA, DEC)
     * @param float $lng_d Longitude en degrés
     * @param float $lat_d Latitude en degrés
     * @return array
     */
    public static function ecliptical2equatorial($lng_d, $lat_d)
    {
        $lng = deg2rad($lng_d);
        $lat = deg2rad($lat_d);

        $y = (sin($lng) * self::dcos(self::EOD)) - (tan($lat) * self::dsin(self::EOD));
        $x = cos($lng);
        $sinDec = (sin($lat) * self::dcos(self::EOD)) + (cos($lat) * self::dsin(self::EOD) * sin($lng));

        $ra = self::deg2hours(rad2deg(atan2($y, $x)));
        $dec = rad2deg(asin($sinDec));

        return [$ra, $dec];
    }

    /**
     * Conversion de coordonnées équatoriales (RA, DEC), en coordoonées horrizontales (Azi, Alt)
     * @param float $dec_d Déclinaison en degrés
     * @param float $lat_d Latitude en degrés
     * @param float $HA_d Heure angulaire local en degrés
     * @param bool $atm_refr Réfraction atmosphérique
     * @param int $pole 0 = sud, 1 = nord
     * @return array Coordonnées [Azi, Alt]
     */
    public static function equatorial2horizontal($dec_d, $lat_d, $HA_d, $atm_refr = false, $pole = 1)
    {
        //$H = deg2rad(hours2deg(LHA($LMST_d, $ra_h)));
        //$H = deg2rad($LMST_d - hours2deg($ra_h));
        $H = deg2rad($HA_d);
        $dec = deg2rad($dec_d);
        $lat = deg2rad($lat_d);

        $y = sin($H);
        $x = (cos($H) * sin($lat)) - (tan($dec) * cos($lat));
        $sinAlt = (sin($lat) * sin($dec)) + (cos($lat) * cos($dec) * cos($H));

        if ($pole) {
            $azi = atan2($y, $x) + deg2rad(180); // Depuis le nord
        } else {
            $azi = atan2($y, $x); // Depuis le sud
        }

        $alt = asin($sinAlt);

        if ($azi < 0) {
            $azi = 2 * self::PI + $azi;
        }

        $azi = rad2deg($azi);
        $alt = rad2deg($alt);

        if ($atm_refr) {
            $alt = self::atmospheric_refraction($alt);
        }

        return [$azi, $alt];
    }

    public static function atmospheric_refraction($alt)
    {
        $R = (1.02 / (self::dtan($alt + (10.3 / ($alt + 5.11))))) + 0.0019279;

        return $alt + AstroMaths::precision($R / self::M_IN_H);
    }

    /**
     * @param $azi_d
     * @param $alt_d
     * @param $lat_d
     * @return array
     */
    public static function horizontal2equatorial($azi_d, $alt_d, $lat_d)
    {
        $azi = deg2rad($azi_d);
        $alt = deg2rad($alt_d);
        $lat = deg2rad($lat_d);

        $y = sin($azi);
        $x = (cos($azi) * sin($lat)) + (tan($alt) * cos($lat));
        $sinDec = (sin($lat) * sin($alt)) - (cos($alt) * cos($lat) * cos($azi));

        $H = rad2deg(atan2($y, $x));
        $dec = rad2deg(asin($sinDec));

        return [$H, $dec];
    }

    public static function kepler($m, $ecc)
    {
        $epsilon = 0.000001;
        $e = $m = deg2rad($m);
        do {
            $delta = $e - $ecc * sin($e) - $m;
            $e -= $delta / (1 - $ecc * cos($e));
        } while (abs($delta) > $epsilon);

        return $e;
    }

    public static function sun_position($JD)
    {
        $T = self::JCentury($JD);
        $T2 = $T * $T;

        $L0 = self::normalize_degrees(280.46646 + (36000.76983 * $T) + (0.0003032 * $T2));
        $M = self::normalize_degrees(357.52911 + (35999.05029 * $T) - (0.0001537 * $T2));
        //$e = self::normalize_degrees(0.016708634 - (0.000042037 * $T) - (0.0000001267 * $T2));

        $C = (1.914602 - (0.004817 * $T) - (0.000014 * $T2)) * self::dsin($M);
        $C += (0.019993 - (0.000101 * $T)) * self::dsin(2 * $M);
        $C += 0.000289 * self::dsin(3 * $M);

        $theta = $L0 + $C;
        //$v = $M + $C;

        $O = 125.04 - (1934.136 * $T);
        $L = self::normalize_degrees($theta - 0.00569 - (0.00478 * self::dsin($O)));
        $obl = self::obliquity($JD, true);
        $obl += 0.00256 * self::dcos($O);

        $ra = self::deg2hours(self::normalize_azimut(rad2deg(atan2(self::dcos($obl) * self::dsin($L), self::dcos($L)))));
        $dec = self::normalize_latitude(rad2deg(asin(self::dsin($obl) * self::dsin($L))));

        return [$ra, $dec];
    }

    /**
     * Calcul de la nutation de la longitude de l'écliptique
     * Sert à ajuster le calcul du GAST
     *
     * Algorithme > http://www.neoprogrammics.com/nutations/Nutation_In_Longitude_And_RA.php
     * & Astronomical Algorithms - Chapter 22 Nutation and the Obliquity of the Ecliptic
     * @param $JD
     * @return float
     */
    public static function nutation_longitude($JD)
    {

        // -----------------------
        // READ JD ARGUMENT VALUE.
        // ERROR IF ARGUMENT IS NOT NUMERIC.

        if (!is_numeric(trim($JD))) {
            return false;
        }

        // -----------------------------------------------------
        // COMPUTE POWERS OF TIME IN JULIAN CENTURIES FROM J2000

        $T1 = self::JCentury($JD);
        $T2 = $T1 * $T1;
        $T3 = $T2 * $T1;

        // -----------------------------------------
        // COMPUTE MEAN LUNAR ELONGATION IN RADIANS.

        $w1 = 297.85036 + 445267.11148 * $T1
            - 0.0019142 * $T2 + ($T3 / 189474);
        $w1 = deg2rad($w1);

        // --------------------------------------
        // COMPUTE MEAN SOLAR ANOMALY IN RADIANS.

        $w2 = 357.52772 + 35999.05034 * $T1
            - 0.0001603 * $T2 - ($T3 / 300000);
        $w2 = deg2rad($w2);

        // --------------------------------------
        // COMPUTE MEAN LUNAR ANOMALY IN RADIANS.

        $w3 = 134.96298 + 477198.867398 * $T1
            + 0.0086972 * $T2 + ($T3 / 56250);
        $w3 = deg2rad($w3);

        // ----------------------------------------------
        // COMPUTE LUNAR ARGUMENT OF LATITUDE IN RADIANS.

        $w4 = 93.27191 + 483202.017538 * $T1
            - 0.0036825 * $T2 + ($T3 / 327270);
        $w4 = deg2rad($w4);

        // ----------------------------------------
        // COMPUTE LUNAR ASCENDING NODE IN RADIANS.

        $w5 = 125.04452 - 1934.136261 * $T1 + 0.0020708 * $T2
            + ($T3 / 450000);
        $w5 = deg2rad($w5);

        // ---------------------------------------------
        // COMPUTE THE NUTATION IN ECLIPTICAL LONGITUDE.

        $w = sin($w5) * (-174.2 * $T1 - 171996);
        $w += sin(2 * ($w4 + $w5 - $w1)) * (-1.6 * $T1 - 13187);
        $w += sin(2 * ($w4 + $w5)) * (-2274 - 0.2 * $T1);
        $w += sin(2 * $w5) * (0.2 * $T1 + 2062);
        $w += sin($w2) * (1426 - 3.4 * $T1);
        $w += sin($w3) * (0.1 * $T1 + 712);
        $w += sin(2 * ($w4 + $w5 - $w1) + $w2) * (1.2 * $T1 - 517);
        $w += sin(2 * $w4 + $w5) * (-0.4 * $T1 - 386);
        $w += sin(2 * ($w4 + $w5 - $w1) - $w2) * (217 - 0.5 * $T1);
        $w += sin(2 * ($w4 - $w1) + $w5) * (129 + 0.1 * $T1);
        $w += sin($w3 + $w5) * (0.1 * $T1 + 63);
        $w += sin($w5 - $w3) * (-0.1 * $T1 - 58);
        $w += sin(2 * $w2) * (17 - 0.1 * $T1);
        $w += sin(2 * ($w2 + $w4 + $w5 - $w1)) * (0.1 * $T1 - 16);
        $w -= 301 * sin(2 * ($w4 + $w5) + $w3);
        $w -= 158 * sin($w3 - 2 * $w1);
        $w += 123 * sin(2 * ($w4 + $w5) - $w3);
        $w += 63 * sin(2 * $w1);
        $w -= 59 * sin(2 * ($w1 + $w4 + $w5) - $w3);
        $w -= 51 * sin(2 * $w4 + $w3 + $w5);
        $w += 48 * sin(2 * ($w3 - $w1));
        $w += 46 * sin(2 * ($w4 - $w3) + $w5);
        $w -= 38 * sin(2 * ($w1 + $w4 + $w5));
        $w -= 31 * sin(2 * ($w3 + $w4 + $w5));
        $w += 29 * sin(2 * $w3);
        $w += 29 * sin(2 * ($w4 + $w5 - $w1) + $w3);
        $w += 26 * sin(2 * $w4);
        $w -= 22 * sin(2 * ($w4 - $w1));
        $w += 21 * sin(2 * $w4 + $w5 - $w3);
        $w += 16 * sin(2 * $w1 - $w3 + $w5);
        $w -= 15 * sin($w2 + $w5);
        $w -= 13 * sin($w3 + $w5 - 2 * $w1);
        $w -= 12 * sin($w5 - $w2);
        $w += 11 * sin(2 * ($w3 - $w4));
        $w -= 10 * sin(2 * ($w4 + $w1) + $w5 - $w3);
        $w -= 8 * sin(2 * ($w4 + $w1 + $w5) + $w3);
        $w += 7 * sin(2 * ($w4 + $w5) + $w2);
        $w -= 7 * sin($w3 - 2 * $w1 + $w2);
        $w -= 7 * sin(2 * ($w4 + $w5) - $w2);
        $w -= 7 * sin(2 * $w1 + 2 * $w4 + $w5);
        $w += 6 * sin(2 * $w1 + $w3);
        $w += 6 * sin(2 * ($w3 + $w4 + $w5 - $w1));
        $w += 6 * sin(2 * ($w4 - $w1) + $w3 + $w5);
        $w -= 6 * sin(2 * ($w1 - $w3) + $w5);
        $w -= 6 * sin(2 * $w1 + $w5);
        $w += 5 * sin($w3 - $w2);
        $w -= 5 * sin(2 * ($w4 - $w1) + $w5 - $w2);
        $w -= 5 * sin($w5 - 2 * $w1);
        $w -= 5 * sin(2 * ($w3 + $w4) + $w5);
        $w += 4 * sin(2 * ($w3 - $w1) + $w5);
        $w += 4 * sin(2 * ($w4 - $w1) + $w2 + $w5);
        $w += 4 * sin($w3 - 2 * $w4);
        $w -= 4 * sin($w3 - $w1);
        $w -= 4 * sin($w2 - 2 * $w1);
        $w -= 4 * sin($w1);
        $w += 3 * sin(2 * $w4 + $w3);
        $w -= 3 * sin(2 * ($w4 + $w5 - $w3));
        $w -= 3 * sin($w3 - $w1 - $w2);
        $w -= 3 * sin($w2 + $w3);
        $w -= 3 * sin(2 * ($w4 + $w5) + $w3 - $w2);
        $w -= 3 * sin(2 * ($w1 + $w4 + $w5) - $w2 - $w3);
        $w -= 3 * sin(2 * ($w4 + $w5) + 3 * $w3);
        $w -= 3 * sin(2 * ($w1 + $w4 + $w5) - $w2);

        // ------------------------------------
        // DONE.  RETURN NUTATION IN ECLIPTICAL
        // LONGITUDE IN DECIMAL DEGREES.

        return ($w / 36000000.0); // * 360 * 10

    }

    /**************************************************
     * Compute the nutation in Epsilon, Delta Epsilon or
     * nutation in obliquity of the ecliptic, in degrees.
     *
     * This is the correction to apply to the MEAN value
     * of the obliquity to obtain the TRUE obliquity.
     *
     * The general precision level is  ±0.001 arc sec.
     *
     * This function is based on the 1980 IAU theory.
     *
     * $JD = JD number corresponding to any date and time
     *
     * $t  = Julian time factor as reckoned from J2000
     * $t2 = $t to the power of 2
     * $t3 = $t to the power of 3
     *
     * ==============================================
     * Nutation correction elements (1980 IAU Theory)
     *
     * $w1 = Mean elongation of the moon from the sun
     * $w2 = Mean anomaly of the sun
     * $w3 = Mean anomaly of the moon
     * $w4 = Argument of latitude of the moon
     * $w5 = Longitude of lunar ascending node
     *
     * $w6 = Nutation series accumulator
     * @param null $JD
     * @return float
     */
    public static Function nutation_obliquity($JD)
    {

        // Compute time factor in Julian centuries as
        // reckoned from J2000 and its powers.
        $T1 = self::JCentury($JD);
        $T2 = $T1 * $T1;
        $T3 = $T2 * $T1;

        // -----------------------------------------
        // Compute mean lunar elongation in radians.

        $w1 = 297.85036 + 445267.11148 * $T1 - 0.0019142 * $T2 + ($T3 / 189474);
        $w1 = deg2rad($w1);

        // --------------------------------------
        // Compute mean solar anomaly in radians.

        $w2 = 357.52772 + 35999.05034 * $T1 - 0.0001603 * $T2 - ($T3 / 300000);
        $w2 = deg2rad($w2);

        // --------------------------------------
        // Compute mean lunar anomaly in radians.

        $w3 = 134.96298 + 477198.867398 * $T1 + 0.0086972 * $T2 + ($T3 / 56250);
        $w3 = deg2rad($w3);

        // ----------------------------------------------
        // Compute lunar argument of latitude in radians.

        $w4 = 93.27191 + 483202.017538 * $T1 - 0.0036825 * $T2 + ($T3 / 327270);
        $w4 = deg2rad($w4);

        // -----------------------------------------------------
        // Compute longitude of lunar ascending node in radians.

        $w5 = 125.04452 - 1934.136261 * $T1 + 0.0020708 * $T2 + ($T3 / 450000);
        $w5 = deg2rad($w5);

        // ---------------------------------------------------
        // Compute the nutation in obliquity (ArcSec * 10000).

        $w6 = cos($w5) * (92025 + 8.9 * $T1);
        $w6 += cos(2 * ($w4 - $w1 + $w5)) * (5736 - 3.1 * $T1);
        $w6 += cos(2 * ($w4 + $w5)) * (977 - 0.5 * $T1);
        $w6 += cos(2 * $w5) * (0.5 * $T1 - 895);
        $w6 += cos($w2) * (54 - 0.1 * $T1);
        $w6 += cos($w2 + 2 * ($w4 - $w1 + $w5)) * (224 - 0.6 * $T1);
        $w6 += cos($w3 + 2 * ($w4 + $w5)) * (129 - 0.1 * $T1);
        $w6 += cos(2 * ($w4 - $w1 + $w5) - $w2) * (0.3 * $T1 - 95);
        $w6 += 200 * cos(2 * $w4 + $w5);
        $w6 -= 70 * cos(2 * ($w4 - $w1) + $w5);
        $w6 -= 53 * cos(2 * ($w4 + $w5) - $w3);
        $w6 -= 33 * cos($w3 + $w5);
        $w6 += 26 * cos(2 * ($w1 + $w4 + $w5) - $w3);
        $w6 += 32 * cos($w5 - $w3);
        $w6 += 27 * cos($w3 + 2 * $w4 + $w5);
        $w6 -= 24 * cos(2 * ($w4 - $w3) + $w5);
        $w6 += 16 * cos(2 * ($w1 + $w4 + $w5));
        $w6 += 13 * cos(2 * ($w3 + $w4 + $w5));
        $w6 -= 12 * cos($w3 + 2 * ($w4 - $w1 + $w5));
        $w6 -= 10 * cos(2 * $w4 + $w5 - $w3);
        $w6 -= 8 * cos(2 * $w1 - $w3 + $w5);
        $w6 += 7 * cos(2 * ($w2 - $w1 + $w4 + $w5));
        $w6 -= 7 * cos($w3);
        $w6 += 9 * cos($w2 + $w5);
        $w6 += 7 * cos($w3 + $w5 - 2 * $w1);
        $w6 += 6 * cos($w5 - $w2);
        $w6 += 5 * cos(2 * ($w1 + $w4) - $w3 + $w5);
        $w6 += 3 * cos($w3 + 2 * ($w4 + $w1 + $w5));
        $w6 -= 3 * cos($w2 + 2 * ($w4 + $w5));
        $w6 += 3 * cos(2 * ($w4 + $w5) - $w2);
        $w6 += 3 * cos(2 * ($w1 + $w4) + $w5);
        $w6 -= 3 * cos(2 * ($w3 + $w4 + $w5 - $w1));
        $w6 -= 3 * cos($w3 + 2 * ($w4 - $w1) + $w5);
        $w6 += 3 * cos(2 * ($w1 - $w3) + $w5);
        $w6 += 3 * cos(2 * $w1 + $w5);
        $w6 += 3 * cos(2 * ($w4 - $w1) + $w5 - $w2);
        $w6 += 3 * cos($w5 - 2 * $w1);
        $w6 += 3 * cos(2 * ($w3 + $w4) + $w5);

        // -----------------------------------
        // Done.  Return nutation in obliquity
        // expressed in degrees.

        return ($w6 / 36000000); // * 360 * 10

    }
}