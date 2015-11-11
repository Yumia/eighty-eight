var HorizonProjection = function (width, height, centerLng, centerLat, fovLng, fovLat) {

    this.width = width;
    this.height = height;
    this.fovLng = (fovLng == undefined) ? 180 : Math.normalize_azimut(fovLng);
    this.fovLat = (fovLat == undefined) ? 90 : fovLat;
    this.centerLng = (centerLng == undefined) ? 0 : Math.normalize_azimut(centerLng);
    this.centerLat = this.fovLat / 2;

    this.limitWest = Math.normalize_azimut(this.centerLng - (this.fovLng / 2));
    this.limitEast = Math.normalize_azimut(this.centerLng + (this.fovLng / 2));
    this.limitSouth = Math.normalize_latitude(this.centerLat - (this.fovLat / 2));
    this.limitNorth = Math.normalize_latitude(this.centerLat + (this.fovLat / 2));

    this.east_point = this.compute(this.limitEast, this.centerLat, false);
    if (this.fovLng > 180 && this.fovLat == 180) {
        this.north_point = this.compute(this.limitEast, this.limitNorth - (this.limitEast - 90), false);
    } else {
        this.north_point = this.compute(this.limitEast, this.limitNorth, false);
    }

    this.ratioX = this.east_point[0]; // AstroMaths::precision
    this.ratioY = this.north_point[1];
    this.degPerPixX = 1 / this.east_point[0];
    this.degPerPixY = 1 / this.north_point[1];

    this.ratioLng = 180 / this.fovLng;
    this.ratioLat = 180 / this.fovLat;
};

HorizonProjection.prototype.adjust = function (X, Y) {
    X += this.ratioX;

    var ratW = this.width / (this.ratioX * 2);
    var ratH = this.height / this.ratioY;
    var rat = Math.max(ratH, ratW);

    var XFill = X * rat;
    X = XFill;

    var YFill = Y * rat;
    Y = this.height - YFill;

    var margin = 0.05 * this.width;

    var inRegionX = Math.belong_to_range(X, 0 - margin, this.width + margin);
    var inRegionY = Math.belong_to_range(Y, 0 - margin, this.height + margin);

    if (!inRegionX || !inRegionY) {
        return false;
    }

    return [X, Y];
};

HorizonProjection.prototype.compute = function (lng, lat, adjust) {
    adjust = (adjust == undefined) ? true : adjust;

    var _lng = lng - this.centerLng;

    var x = Math.dcos(lat) * Math.dsin(_lng);
    var y = Math.dsin(lat);
    var z = Math.dcos(lat) * Math.dcos(_lng);

    if (z == -1) {
        return false;
    }

    var X = x / (1 + z);
    var Y = y / (1 + z);

    if (adjust) {
        return this.adjust(X, Y);
    } else {
        return [X, Y];
    }
};