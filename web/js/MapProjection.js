var MapProjection = function (width, height, centerLng, centerLat, fovLng, fovLat) {

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

    /*this.east_point = this.compute(this.limitEast, this.centerLat, false);
    if (this.fovLng > 180 && this.fovLat == 180) {
        this.north_point = this.compute(this.limitEast, this.limitNorth - (this.limitEast - 90), false);
    } else {
        this.north_point = this.compute(this.limitEast, this.limitNorth, false);
    }*/

    this.north_point = [0, 1];
    this.east_point = [1, 0];

    this.ratioX = this.east_point[0]; // AstroMaths::precision
    this.ratioY = this.north_point[1];
    this.degPerPixX = 1 / this.east_point[0];
    this.degPerPixY = 1 / this.north_point[1];

    this.ratioLng = 180 / this.fovLng;
    this.ratioLat = 180 / this.fovLat;
};

MapProjection.prototype.adjust = function (X, Y) {
    // Center X & Y
    X += this.ratioX;
    Y += this.ratioY;

    // Adjust ratios
    // Now we fit to the plane dimensions given
    X = X * 0.5 * this.degPerPixX;
    Y = Y * 0.5 * this.degPerPixY;

    // Compute finale coords
    X = (X * this.width);
    Y = (this.height - (Y * this.height));

    return [X, Y];
};

MapProjection.prototype.compute = function (lng, lat, adjust) {
    adjust = (adjust == undefined) ? true : adjust;

    if(lat < 0){
        return false;
    }

    var _lng = lng - this.centerLng + 90;
    var _lat = (90 - lat) / 90;

    var X = Math.dcos(_lng) * _lat;
    var Y = Math.dsin(_lng) * _lat;

    if(adjust){
        return this.adjust(X, Y);
    } else {
        return [X, Y];
    }
};