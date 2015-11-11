Math.rad2deg = function (rad) {
    return 180 * (rad / Math.PI);
};

Math.deg2rad = function (deg) {
    return Math.PI * (deg / 180);
};

Math.dcos = function (deg) {
    return Math.cos(Math.deg2rad(deg));
};

Math.dsin = function (deg) {
    return Math.sin(Math.deg2rad(deg));
};

Math.dtan = function (deg) {
    return Math.tan(Math.deg2rad(deg));
};

Math.normalize_latitude = function (lat) {
    while (lat < -90 || lat > 90) {
        lat = (lat < -90) ? lat + 180 : lat - 180;
    }

    return lat;
};

Math.normalize_longitude = function (lng) {
    while (lng < -180 || lng > 180) {
        lng = (lng < -180) ? lng + 360 : lng - 360;
    }

    return lng;
};

Math.normalize_azimut = function (lng, inc) {
    inc = (inc == undefined) ? false : inc;
    return Math.normalize_degrees(lng, inc);
};

Math.normalize_degrees = function (deg, inc) {
    inc = (inc == undefined) ? false : inc;
    while (deg < 0 || (inc && deg > 360) || (!inc && deg >= 360)) {
        deg = (deg < 0) ? deg + 360 : deg - 360;
    }

    return deg;
};

Math.belong_to_range = function (v, a, b, min, max) {
    min = (min == undefined) ? null : min;
    max = (max == undefined) ? null : max;

    if (a < b) {
        return (a <= v) && (v <= b);
    } else if (a > b) {
        if (max == null || min == null) {
            return false;
        }

        return (((a <= v) && (v <= max)) || ((min <= v) && (v <= b)));
    } else {
        if (max == null || min == null) {
            return false;
        }

        return (min <= v) && (v <= max);
    }
};

Math.JYEAR = 1582;
Math.JMONTH = 10;
Math.JDAY = 4;
Math.D_IN_JCENT = 36525.0;
Math.D_IN_JY = 365.25;
Math.D_IN_JM = 30.6001;
Math.J2000 = 2451545.0;

/**
 * @return {number}
 */
Math.HA = function(ST, ra, lng){
    lng = (lng == undefined) ? null : lng;

    var HA = ST - ((ra * 360) / 24);
    HA -= (lng != null) ? lng : 0;
    HA = Math.normalize_degrees(HA);

    return HA;
};

Math.equatorial2horizontal = function(dec_d, lat_d, HA_d, atm_refr, pole) {
    atm_refr = (atm_refr == undefined) ? false : atm_refr;
    pole = (pole == undefined) ? 1 : pole;

    var azi, alt;

    var H = Math.deg2rad(HA_d);
    var dec = Math.deg2rad(dec_d);
    var lat = Math.deg2rad(lat_d);

    var y = Math.sin(H);
    var x = (Math.cos(H) * Math.sin(lat)) - (Math.tan(dec) * Math.cos(lat));
    var sinAlt = (Math.sin(lat) * Math.sin(dec)) + (Math.cos(lat) * Math.cos(dec) * Math.cos(H));

    if (pole == 1) {
        azi = Math.atan2(y, x) + Math.deg2rad(180); // Depuis le nord
    } else {
        azi = Math.atan2(y, x); // Depuis le sud
    }

    alt = Math.asin(sinAlt);

    if (azi < 0) {
        azi = 2 * Math.PI + azi;
    }

    azi = Math.rad2deg(azi);
    alt = Math.rad2deg(alt);

    if (atm_refr == true) {
        alt = Math.atmospheric_refraction(alt);
    }

    return [azi, alt];
};

Math.atmospheric_refraction = function(alt) {
    var R = (1.02 / (Math.dtan(alt + (10.3 / (alt + 5.11))))) + 0.0019279;
    return alt + (R / 60); // Precision
};

Math.is_julian = function(year, month, day){
    return !!((year < Math.JYEAR) || ((year == Math.JYEAR && month < Math.JMONTH)) || (year == Math.JYEAR && month == Math.JMONTH && day <= Math.JDAY));
};

/**
 * @return {number}
 */
Math.UTC_to_JD = function(year, month, day){
    var is_julian = Math.is_julian(year, month, day);

    if(month <= 2){
        year -= 1;
        month += 12;
    }

    var A = Math.floor(year / 100);
    var B = (is_julian) ? 0 : 2 - A + Math.floor(A / 4);
    return Math.floor(this.D_IN_JY * (year + 4716)) + Math.floor(this.D_IN_JM * (month + 1)) + day + B - 1524.5;
};

/**
 * @return {number}
 */
Math.JD = function (cut) {
    cut = (cut == undefined) ? false : cut;
    var date = new Date();

    var year = date.getUTCFullYear();
    var month = date.getUTCMonth() + 1;
    var day = date.getUTCDate();
    var hours = date.getUTCHours();
    var minutes = date.getUTCMinutes();
    var secondes = date.getUTCSeconds();

    if (cut == false) {
        day += (hours / 24) + (minutes / (60 * 24)) + (secondes / (60 * 60 * 24));
    }

    return Math.UTC_to_JD(year, month, day);
};

/**
 * @return {number}
 */
Math.JD_from_J2000 = function(JD){
    return JD - Math.J2000;
};

/**
 * @return {number}
 */
Math.JCentury = function(JD){
    JD = (JD == undefined) ? Math.JD() : JD;
    return Math.JD_from_J2000(JD) / Math.D_IN_JCENT;
};

/**
 * @return {number}
 */
Math.GMST = function (JD, cut, apparent) {
    cut = (cut == undefined) ? false : cut;
    JD = (JD == undefined) ? Math.JD(cut) : JD;
    apparent = (apparent == undefined) ? false : apparent;

    var T = Math.JCentury(JD);

    var a = 280.46061837;
    var b = 360.98564736629 * Math.JD_from_J2000(JD);
    var c = 0.000387933 * Math.pow(T, 2);
    var d = Math.pow(T, 3) / 38710000;

    var GST = a + b + c - d;

    if(apparent == true){
        // TODO ... (flemme) ^_^
    }

    GST = Math.normalize_degrees(GST);

    return GST;
};

/**
 * @return {number}
 */
Math.GAST = function (JD, cut) {
    JD = (JD == undefined) ? null : JD;
    cut = (cut == undefined) ? false : cut;
    return Math.GMST(JD, cut);
};