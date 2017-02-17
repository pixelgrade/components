(function ($, window, undefined) {
    var windowWidth 		= window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
        windowHeight 		= window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight,
        $window             = $(window);

    /* --- Multipage Navigator Init --- */

    var Multipage_Navigator = {
        // variables
        $el: $('<div class="multipage-navigator"></div>'),
        sectionSelector: '.article--page header',
        scrollDuration: 300,

        // private
        currentSelected: 0,
        lastSelected: 0,
        isWhite: true,
        wasWhite: true,
        initialized: false,
        timeline: new TimelineMax({paused: true}),
        nextTop: 0,
        footer: null,
        footerTop: 0,

        initialize: function () {

            var that = this,
                $multipage_navigator = this.$el;

            this.initialized = true;
            this.$sections = $(that.sectionSelector);

            // @todo check logic here
            this.footer = $('.sidebar--footer__dark');

            if (this.footer.length) {
                this.footerTop = this.footer.offset().top;
            }

            if (this.$sections.length < 2) {
                return;
            }

            for (var index = 0; index < this.$sections.length; index++) {

                var $section = $(that.$sections[index]),
                    sectionTop = $section.offset().top,
                    sectionHeight = $section.outerHeight(),
                    $button = $('<a href="#" class="multipage-navigator__item"><div class="bullet"></div></a>');

                if ($section.css('display') == 'none') {

                    if (!$section.next().is('.article--page')) {
                        that.$sections.splice(index, 1);
                        index--;
                        continue;
                    } else {
                        sectionTop = that.nextTop;
                    }
                } else {
                    that.nextTop += sectionHeight;
                }

                if ($section.next().is('.article--page')) {
                    that.nextTop += $section.next().outerHeight();
                }

                $button.appendTo($multipage_navigator);
                $button.data('scrollTo', sectionTop - windowHeight / 2 + sectionHeight / 2);
                $section.data('offsetTop', sectionTop).data('height', sectionHeight);

                $section.data('display', $section.css('display'));

                // closures
                (function ($newButton) {
                    $newButton.on('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        smoothScrollTo($newButton.data('scrollTo'));

                        return false;
                    });
                })($button);

            }

            this.$selected = $('<div class="multipage-navigator__item  multipage-navigator__item--selected"><div class="bullet"></div></div>').appendTo($multipage_navigator);
            this.$selectedBullet = this.$selected.find('.bullet');

            this.timeline.add(TweenMax.to(that.$selectedBullet, 0, {}));

            this.timeline.add(TweenMax.to(that.$selectedBullet, 0.1, {
                borderTopLeftRadius: 20,
                borderTopRightRadius: 20,
                borderBottomLeftRadius: 50,
                borderBottomRightRadius: 50,
                'scaleY': 2,
                'scaleX': 0.6
            }));

            this.timeline.add(TweenMax.to(that.$selectedBullet, 0.1, {
                borderTopLeftRadius: 50,
                borderTopRightRadius: 50,
                borderBottomLeftRadius: 50,
                borderBottomRightRadius: 50,
                'scaleY': 1,
                'scaleX': 1
            }));

            this.timeline.add(TweenMax.to(that.$selectedBullet, 0, {
                'scale': 1.2
            }));


            $multipage_navigator.css({'margin-top': -1 * $multipage_navigator.height() / 2}).prependTo("body");

            this.update();

            $('.multipage-navigator__item').each(function (i, obj) {

                var items = $('.multipage-navigator__item').length,
                    stagger = 3000 + i * 400,
                    $obj = $(obj);

                if ($obj.is('.multipage-navigator__item--selected')) {
                    stagger = stagger + items * 100;
                }

                setTimeout(function () {
                    TweenMax.fromTo($obj, 1, {opacity: 0, scale: 0.7}, {
                        opacity: 1.25,
                        scale: 1,
                        ease: Elastic.easeOut
                    });
                }, stagger);
            });

            if ($multipage_navigator.hasClass('multipage-navigator--transparent'))
                TweenMax.to($multipage_navigator, 2, {opacity: .2});
            else
                TweenMax.to($multipage_navigator, .3, {opacity: 1});
        },

        update: function () {
            var that = this,
                $multipage_navigator = this.$el;

            if (!this.initialized) {
                //            this.initialize();
                return;
            }

            // loop through each header and find current state
            this.$sections.each(function (i, element) {

                var $section = $(element),
                    sectionTop = $section.data('offsetTop'),
                    sectionBottom = sectionTop + $section.data('height'),
                    multipage_navigatorMiddle = latestKnownScrollY + (windowHeight / 2);

                // if there's no header

                if ($section.data('display') == 'none') {
                    sectionBottom = sectionTop;
                    if (!$section.next().is('.article--page')) {
                        return;
                    }
                }

                if (multipage_navigatorMiddle > sectionTop) {
                    that.currentSelected = i;
                    that.isWhite = true;

                    if (multipage_navigatorMiddle > sectionBottom) {
                        that.isWhite = false;
                    }
                }

            });

            if (this.footerTop != 0 && this.footerTop < latestKnownScrollY + (windowHeight / 2)) {
                this.isWhite = true;
            }

            // if the multipage-navigator's indicator has to be moved
            // then move it accordingly and update state
            if (this.lastSelected != this.currentSelected) {
                this.lastSelected = this.currentSelected;
                TweenMax.to(this.$selected, 0.3, {top: 24 * that.currentSelected});
                that.timeline.tweenFromTo(0, 0.3);
                //            that.timeline.play();
            }

            // if the multipage-navigator's color has to be changed
            // then change it accordingly and update state
            if (this.wasWhite != this.isWhite) {
                this.wasWhite = this.isWhite;
                $multipage_navigator.toggleClass('multipage-navigator--black', !that.isWhite);
            }
        }
    };

    function smoothScrollTo(y, speed) {

        speed = typeof speed == "undefined" ? 1 : speed;

        var distance = Math.abs(latestKnownScrollY - y),
            time     = speed * distance / 2000;

        TweenMax.to($(window), time, {scrollTo: {y: y, autoKill: true, ease: Quint.easeInOut}});
    }

    /* ====== ON WINDOW LOAD ====== */

    $window.load(function() {

        setTimeout(function () {
            Multipage_Navigator.initialize();
        }, 60);

        loop();
    });

    function updateStuff() {
        if ( windowWidth >= 900 ) {
            Multipage_Navigator.update();
        }
    }

    window.latestKnownScrollY = window.pageYOffset;

    var newScrollY = latestKnownScrollY,
        ticking = false;

    $window.scroll(function() {
        newScrollY = window.pageYOffset;
    });

    function loop() {
        // Avoid calculations if not needed
        if (latestKnownScrollY !== newScrollY) {
            latestKnownScrollY = newScrollY;
            updateStuff();
        }
        requestAnimationFrame(loop);
    }

}) (jQuery, window);