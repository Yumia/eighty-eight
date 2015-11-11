$(document).ready(function () {

    function successPosition(pos) {
        $.ajax({
            url: geolocation + "/geolocated",
            method: 'POST',
            dataType: 'html',
            data: {
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
                time: pos.timestamp
            },
            success: function (data) {
                $content.html(data);
                setTimeout(function () {
                    window.location.href = site_url;
                }, 2000);
            }
        });
    }

    function errorPosition(err) {
        if (errCode != 0) {
            return false;
        }

        var message = "";
        errCode = (err.code == undefined) ? err : err.code;

        switch (errCode) {
            case 1:
                message = "Permission was denied, impossible to locate your position.";
                break;
            case 2:
                message = "Could not establish your location, no response received.";
                break;
            case 3:
                message = "Request is anormaly long, timeout reached trying to locate you.";
                break;
            case 4:
                message = "Geolocation is not available.";
                break;
        }

        $.ajax({
            url: geolocation + "/error",
            dataType: 'html',
            success: function (data) {
                $content.html(data);
                $('.lead', $content).text(message);
            }
        });

    }

    var $loading = $('#loading');
    var $bg = $('#bg');
    var btnGeoloc = '#btn-geolocation';
    var btnNoGeoloc = '#btn-no-geolocation';
    var $content = $('#content');
    var errCode = 0;

    window.setTimeout(start, 3000);

    function start(){
        $loading.fadeOut(100);
        $bg.delay(200).fadeIn();
        $content.delay(500).fadeIn();
    }

    $(document).on('click', btnGeoloc, function(e) {
        errCode = 0;
        e.preventDefault();

        $.ajax({
            url: geolocation + "/geolocating",
            dataType: 'html',
            success: function (data) {
                $content.html(data);

                if (!navigator.geolocation) {
                    errorPosition(4);
                } else {
                    navigator.geolocation.getCurrentPosition(successPosition, errorPosition, {
                        timeout: 10000,
                        maximumAge: Infinity,
                        enableHighAccuracy: false
                    });
                }

            }
        });
    });

    $(document).on('click', btnNoGeoloc, function(e) {
        e.preventDefault();

        $.ajax({
            url: geolocation + "/geolocated",
            method : 'POST',
            data: {
                time: Math.floor(+new Date() / 1000)
            },
            success: function () {
                window.location.href = $(btnNoGeoloc).attr('href');
            }
        });
    });

});