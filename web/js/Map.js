var Map = {

    $svg: null,
    Snap: null,

    width: 0,
    height: 0,
    centerLng: 0,
    centerLat: 0,
    fovLng: fovLng,
    fovLat: 90,

    GAST: 0,
    proj: undefined,

    data: [],
    refs: [],

    animInit: false,

    init : function($svg){
        // On a besoin 'une cible SVG
        if (!$svg.length) {
            return false;
        }

        this.$svg = $svg;
        this.Snap = new Snap($svg[0]); // Instanciation de Snap.svg

        // Premier chargement et initialisation du ticker
        this.setDimensions();
        this.setTimeout(true);

        $(window).on('resize', $.throttle(100, function () {
            Map.setDimensions();
        }));
    },

    getCoords: function (ra, dec, proj) {
        proj = (proj == undefined) ? this.proj : proj;

        var HA = Math.HA(this.GAST, ra, this.data.lng);
        var aziAlt = Math.equatorial2horizontal(dec, this.data.lat, HA, true);
        return proj.compute(aziAlt[0], aziAlt[1]);
    },

    setTimeout: function (timeout) {
        window.setTimeout(function () {
            Map.update(timeout);
        }, 1000);
    },

    loadData: function () {
        $.ajax({
            url: site + "/map/data",
            dataType: 'json',
            success: function (data) {
                Map.data = data;
                Map.update();
                console.log(Map.data);
            }
        });
    },

    hover_in: function (e) {
        var code = this.attr('code');

        if ('hover' in Map.refs[code]) {
            return false;
        }

        var bound = this.node.getBoundingClientRect();

        var cx = bound.left + ((bound.right - bound.left) / 2);
        var cy = bound.top + ((bound.bottom - bound.top) / 2);
        var r = 0.5 * Math.sqrt(Math.pow(bound.width, 2) + Math.pow(bound.height, 2));

        cx-= ($(window).width() - Map.width) / 2;
        cy-= ($(window).height() - Map.height) / 2;

        var c = Map.Snap.circle(cx, cy, r);
        c.attr({
            fill: "#ffffff",
            stroke: "none",
            class: "hover"
        });

        this.prepend(c);
        Map.refs[code]['hover'] = c;
        Map.Snap.append(this);
    },

    hover_out: function (e) {
        var code = this.attr('code');
        var c = Map.refs[code]['hover'];

        c.remove();
        delete Map.refs[code]['hover'];
    },

    update: function (timeout) {
        this.proj = new MapProjection(this.width, this.height, this.centerLng, this.centerLat, this.fovLng, this.fovLat);
        this.GAST = Math.GAST();

        if (this.data.length == 0) {
            this.loadData();
            return;
        }

        var created = 0,
            moved = 0,
            deleted = 0,
            groups = [];

        var x, x1, x2, _x, _x1, _x2,
            y, y1, y2, _y, _y1, _y2,
            XY, XY1, XY2,
            BBox, _width, _height,
            circle, rect, text, hover, path,
            i, j;

        if (!('lines' in this.refs)) {
            this.refs['lines'] = [];
        } else {
            this.refs['lines']['lat'].remove();
            this.refs['lines']['lng'].remove();
        }

        this.refs['lines']['lat'] = this.Snap.g().prependTo(this.Snap);
        this.refs['lines']['lng'] = this.Snap.g().prependTo(this.Snap);

        var prev;

        // Latitudes
        path = "";
        for (i = -8; i < 9; i++) {
            prev = undefined;

            for (j = 0; j <= 36; j += 0.5) {
                XY = this.getCoords(j, i * 10);

                if (XY == false) {
                    prev = undefined;
                    continue;
                }

                x = Math.round(XY[0]);
                y = Math.round(XY[1]);

                if (prev == undefined) {
                    path += "M" + x + " " + y;
                } else {
                    path += "L" + x + " " + y;
                }

                prev = XY;
            }
        }

        this.Snap.path(path).attr({
            fill: 'none',
            stroke: '#d8d8d8'
        }).appendTo(this.refs['lines']['lat']);
        created++;

        // Longitudes
        path = "";
        for (i = 0; i < 360; i += 5) {
            prev = undefined;

            for (j = -9; j <= 9; j += 0.5) {
                XY = this.getCoords(i, j * 10);

                if (XY == false) {
                    prev = undefined;
                    continue;
                }

                x = Math.round(XY[0]);
                y = Math.round(XY[1]);

                if (prev == undefined) {
                    path += "M" + x + " " + y;
                } else {
                    path += "L" + x + " " + y;
                }

                prev = XY;
            }
        }

        this.Snap.path(path).attr({
            fill: 'none',
            stroke: '#d8d8d8'
        }).appendTo(this.refs['lines']['lng']);
        created++;


        /**
         * Parcours des données pour génération/màj du SVG
         */
        for (var code in this.data.const) {
            var constellation = this.data.const[code];

            // Initialisation des références si elles n'existent pas
            if (!(code in this.refs)) {
                this.refs[code] = [];
                this.refs[code]['stars'] = [];
                this.refs[code]['lines'] = [];
                this.refs[code]['g'] = this.Snap.el("a", {
                    class: "group",
                    code: code,
                    'xlink:href': site + 'constellations/view/' + constellation.code
                }).hover(this.hover_in, this.hover_out);
            }

            /**
             * I) Position du nom des constellations
             */
            XY = this.getCoords(constellation.ra, constellation.dec);

            // Si les coordonnées sont valides
            // - soit on les met à jour
            // - soit on crée l'élément
            // Sinon, si on possède un référence, on la détruit
            if (XY != false) {
                x = Math.round(XY[0]);
                y = Math.round(XY[1]);

                // Si on possède déjà une référence, on tente de la mettre à jour
                if ('name' in this.refs[code]) {
                    text = this.refs[code]['name']['text'];
                    rect = this.refs[code]['name']['rect'];

                    _x = text.attr('x');
                    _y = text.attr('y');

                    BBox = text.node.getBoundingClientRect();
                    _width = rect.attr('width');
                    _height = rect.attr('height');

                    // Si des paramètes ont changés, on met à jour
                    if (x != _x || y != _y || BBox.width != _width || BBox.height != _height) {
                        text.attr({
                            x: x,
                            y: y
                        });

                        rect.attr({
                            x: x - 10,
                            y: y - BBox.height + 3 - 5,
                            width: Math.round(BBox.width) + 20,
                            height: BBox.height + 10
                        });

                        moved++;
                    }
                } else {
                    // Préparation des références
                    this.refs[code]['name'] = [];

                    // Texte
                    text = this.Snap.text(x, y, constellation.name.toUpperCase());

                    // Boîte
                    BBox = text.node.getBoundingClientRect();
                    rect = this.Snap.rect(
                        x - 10,
                        y - BBox.height + 3 - 5,
                        Math.round(BBox.width) + 20,
                        BBox.height + 10
                    );

                    // Ajout des références
                    this.refs[code]['g'].add(rect);
                    this.refs[code]['g'].add(text);
                    this.refs[code]['name']['rect'] = rect;
                    this.refs[code]['name']['text'] = text;
                }
            } else {
                if ('name' in this.refs[code]) {
                    this.refs[code]['name']['text'].remove();
                    delete this.refs[code]['name'];

                    deleted++;
                }
            }

            // Si aucun point valide, on supprime le groupe après
            var valids = 0;

            /**
             * LINES
             */
            for (i = 0; i < constellation.lines.length; i++) {
                s1 = this.data.stars[constellation.lines[i][0]];
                s2 = this.data.stars[constellation.lines[i][1]];

                XY1 = this.getCoords(s1.ra, s1.dec);
                XY2 = this.getCoords(s2.ra, s2.dec);

                if (XY1 != false && XY2 != false) {
                    x1 = Math.round(XY1[0]);
                    y1 = Math.round(XY1[1]);

                    x2 = Math.round(XY2[0]);
                    y2 = Math.round(XY2[1]);

                    // Does ref already exists ?
                    if (i in this.refs[code]['lines']) {
                        line = this.refs[code]['lines'][i];

                        _x1 = line.attr('x1');
                        _y1 = line.attr('y1');
                        _x2 = line.attr('x2');
                        _y2 = line.attr('y2');

                        if (x1 != _x1 || y1 != _y1 || x2 != _x2 || y2 != _y2) {
                            line.attr({
                                x1: x1,
                                y1: y1,
                                x2: x2,
                                y2: y2
                            });

                            /*line.stop().animate({
                             x1: x1,
                             y1: y1,
                             x2: x2,
                             y2: y2
                             }, 500);*/

                            moved++;
                        }
                    } else {
                        line = this.Snap.line(x1, y1, x2, y2);
                        line.attr();

                        //this.refs[code]['g'].add(line);
                        line.prependTo(this.refs[code]['g']);
                        this.refs[code]['lines'][i] = line;

                        created++;
                    }
                } else {
                    if (i in this.refs[code]['lines']) {
                        this.refs[code]['lines'][i].remove();
                        delete this.refs[code]['lines'][i];

                        deleted++;
                    }
                }
            }

            /**
             * STARS
             */
            for (i = 0; i < constellation.stars.length; i++) {
                star = this.data.stars[constellation.stars[i]];

                XY = this.getCoords(star.ra, star.dec);

                if (XY != false) {
                    valids++;

                    var x = Math.round(XY[0]);
                    var y = Math.round(XY[1]);

                    // Does ref already exists ?
                    if (star.id in this.refs[code]['stars']) {
                        circle = this.refs[code]['stars'][star.id];

                        var _x = circle.attr('cx');
                        var _y = circle.attr('cy');

                        if (x != _x || y != _y) {
                            circle.attr({
                                cx: x,
                                cy: y
                            });

                            /*circle.stop().animate({
                             cx: x,
                             cy: y
                             }, 500);*/

                            moved++;
                        }
                    } else {

                        circle = this.Snap.circle(x, y, 2);
                        circle.attr({
                            r: star.mag,
                            fill: "#" + star.color,
                            class: "star"
                        });

                        this.refs[code]['g'].add(circle);
                        this.refs[code]['stars'][star.id] = circle;

                        if ('name' in this.refs[code]) {
                            circle.insertBefore(this.refs[code]['name']['rect']);
                        }

                        created++;
                    }
                } else {
                    if (star.id in this.refs[code]['stars']) {
                        this.refs[code]['stars'][star.id].remove();
                        delete this.refs[code]['stars'][star.id];

                        deleted++;
                    }
                }
            }

            // On supprime les groupes vides
            if (valids == 0) {
                this.refs[code]['g'].remove();
                delete this.refs[code]
            } else {
                groups.push(this.refs[code]['g'].node);
            }
        }

        // Animation d'intro
        if (this.animInit == false) {
            TweenMax.staggerFromTo(groups, .5, {opacity: 0}, {opacity: 1}, 0.02);
            this.animInit = true;
        }

        // debug
        //console.log("created:" + created + ", moved:" + moved + ", deleted:" + deleted);

        if (timeout == true) {
            this.setTimeout(true);
        }
    },

    setDimensions: function () {
        var w = $(window).width();
        var h = $(window).height();
        var d = Math.min(w, h);

        var rat = 0.75;
        this.width = d * rat;
        this.height = d * rat;

        this.$svg.attr('width', this.width).attr('height', this.height);

        this.update();
    }
};