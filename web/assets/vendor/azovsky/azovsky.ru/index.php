﻿<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Отдых в Крыму на Азовском море</title>
    <meta name="description"
          content="Курортная сеть «Азовский» предлагает отдых в Крыму на Азовском море для всей семьи по доступным ценам."/>
    <META name="keywords" content="отдых в Крыму, отдых в Крыму на Азовском море">
    <meta name="language" content="ru,russian"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans&subset=latin,cyrillic' rel='stylesheet'
          type='text/css'>

    <script src="http://code.jquery.com/jquery-1.12.0.min.js"></script>

    <!-- Bootstrap -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://azovsky.ru/css/searchstyle.css" rel="stylesheet">
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="../../font-awesome/css/font-awesome.css">

    <link rel="stylesheet" href="http://azovsky.ru/js/fancy/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen"/>
    <link rel="stylesheet" href="http://azovsky.ru/js/fancy/helpers/jquery.fancybox-buttons.css?v=1.0.5" type="text/css" media="screen"/>
    <link rel="stylesheet" href="http://azovsky.ru/js/fancy/helpers/jquery.fancybox-thumbs.css?v=1.0.7" type="text/css" media="screen"/>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <!-- Google Analytics -->
    <script>
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
        ga('create', 'UA-24480210-2', 'auto');
        ga('require', 'displayfeatures');
        ga('require', 'linkid', 'linkid.js');
        /* Openstat parser v3 minimized */
        var op = {
            _params: {}, _parsed: !1, _decode64: function (a) {
                if ('function' == typeof window.atob)return atob(a);
                var r, t, n, i, e, s, o, p, d = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=', h = 0, c = 0, f = '', m = [];
                if (!a)return a;
                a += '';
                do i = d.indexOf(a.charAt(h++)), e = d.indexOf(a.charAt(h++)), s = d.indexOf(a.charAt(h++)), o = d.indexOf(a.charAt(h++)), p = i << 18 | e << 12 | s << 6 | o, r = p >> 16 & 255, t = p >> 8 & 255, n = 255 & p, m[c++] = 64 == s ? String.fromCharCode(r) : 64 == o ? String.fromCharCode(r, t) : String.fromCharCode(r, t, n); while (h < a.length);
                return f = m.join('')
            }, _parse: function () {
                var a = window.location.search.substr(1), r = a.split('&');
                this._params = {};
                for (var t = 0; t < r.length; t++) {
                    var n = r[t].split('=');
                    this._params[n[0]] = n[1]
                }
                this._parsed = !0
            }, hasMarker: function () {
                return window.location.search.indexOf('utm_') > 0 ? !1 : (this._parsed || this._parse(), 'undefined' != typeof this._params._openstat ? !0 : !1)
            }, buildCampaignParams: function () {
                if (!this.hasMarker())return !1;
                var a = this._decode64(this._params._openstat), r = a.split(';');
                return {
                    campaignName: r[1],
                    campaignSource: r[0],
                    campaignMedium: 'cpc',
                    campaignContent: r[2] + ' (' + r[3] + ')'
                }
            }
        };
        var cp = op.hasMarker() ? op.buildCampaignParams() : {};
        ga('send', 'pageview', cp);

        /* Accurate bounce rate by time */
        document.referrer && 0 == document.referrer.split('/')[2].indexOf(location.hostname) || setTimeout(function () {
            ga('send', 'event', 'Сеансы', 'Сеансы без отказов', location.pathname)
        }, 15000);
        /* Заглушка для СТАРОГО кода ga.js. Если Вы его не использовали, этот код можно НЕ ВКЛЮЧАТЬ! */
        window._gaq = {
            eCommerceIncluded: false, vars: [], push: function (a) {
                var c = "GA STUB";
                switch (a[0]) {
                    case"_trackPageview":
                        ga("send", "pageview", a[1]);
                        break;
                    case"_trackEvent":
                        ga("send", "event", a[1], a[2], a[3], a[4]);
                        break;
                    case"_addTrans":
                        if (!this.eCommerceIncluded) {
                            this.eCommerceIncluded = true;
                            ga("require", "ecommerce", "ecommerce.js")
                        }
                        ga("ecommerce:addTransaction", {
                            id: a[1],
                            affiliation: a[2],
                            revenue: a[3],
                            tax: a[4],
                            shipping: a[5]
                        });
                        break;
                    case"_addItem":
                        ga("ecommerce:addItem", {
                            id: a[1],
                            sku: a[2],
                            name: a[3],
                            category: a[4],
                            price: a[5],
                            quantity: a[6]
                        });
                        break;
                    case"_trackTrans":
                        ga("ecommerce:send");
                        break;
                    case"_setCustomVar":
                        for (var b = 0; b < this.vars.length; b++) {
                            if (this.vars[b].slot == a[1]) {
                                ga("set", this.vars[b].name, a[3]);
                                break
                            }
                        }
                        var d = "index: " + a[1] + ", name: " + a[2] + ", value: " + a[3] + ", opt_scope: " + a[4];
                        ga("send", "event", c, "Непривязаянная переменная", d, location.pathname);
                        break;
                    case"_trackSocial":
                        ga("send", "social", a[1], a[2], a[3]);
                        break;
                    case"_trackPageLoadTime":
                        ga("send", "event", c, "Вызов устаревшего метода", a[0], location.pathname);
                        break;
                    default:
                        ga("send", "event", c, "Вызов неизвестного метода", a[0], location.pathname)
                }
            }
        };
    </script><!--/Google Analytics -->
    <script type="text/javascript">
        function ct(w, d, e, c) {
            var a = 'all', b = 'tou', src = b + 'c' + 'h';
            src = 'm' + 'o' + 'd.c' + a + src;
            var jsHost = "https://" + src, s = d.createElement(e), p = d.getElementsByTagName(e)[0];
            s.async = 1;
            s.src = jsHost + "." + "r" + "u/d_client.js?param;" + (c ? "client_id" + c + ";" : "") + "ref" + escape(d.referrer) + ";url" + escape(d.URL) + ";cook" + escape(d.cookie) + ";";
            if (!w.jQuery) {
                var jq = d.createElement(e);
                jq.src = jsHost + "." + "r" + 'u/js/jquery-1.7.min.js';
                jq.onload = function () {
                    p.parentNode.insertBefore(s, p);
                };
                p.parentNode.insertBefore(jq, p);
            } else {
                p.parentNode.insertBefore(s, p);
            }
        }
        if (!!window.GoogleAnalyticsObject) {
            window[window.GoogleAnalyticsObject](function (tracker) {
                if (!!window[window.GoogleAnalyticsObject].getAll()[0]) {
                    ct(window, document, 'script', window[window.GoogleAnalyticsObject].getAll()[0].get('clientId'))
                }
                else {
                    ct(window, document, 'script', null);
                }
            });
        } else {
            ct(window, document, 'script', null);
        }
    </script>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function (w, c) {
            (w[c] = w[c] || []).push(function () {
                try {
                    w.yaCounter10885255 = new Ya.Metrika({id: 10885255, enableAll: true, webvisor: true});
                }
                catch (e) {
                }
            });
        })(window, "yandex_metrika_callbacks");
    </script>
    <script src="http://mc.yandex.ru/metrika/watch.js" type="text/javascript" defer></script>
    <!--/ Yandex.Metrika counter -->
</head>

<body>


<header>
    <div class="container">
        <div class="col-md-3 col-sm-12 col-xs-12 logo"><a name="ttop" href="index.html" title="Главная"><img
                src="images/logo-new-1.png" alt="Азовский - логотип"></a></div>
        <div class="col-md-9 col-sm-12 col-xs-12">
            <div class="row infohd">
                <div class="col-md-5 col-sm-7 col-xs-7" style="padding-right:0px;">для регионов (звонок
                    бесплатный)<br><span style="font-size:24px;"><a href="tel:+78007751541">8 (800) 775-15-41</a></span>
                </div>
                <div class="col-md-4 zaiav" data-toggle="modal" data-target="#myModal">оставьте заявку и мы
                    перезвоним<br><a href="index.html#"
                                     title="Заказ обратного звонка"><span>свяжитесь со мной</span></a></div>
                <div class="col-md-3 col-sm-5 col-xs-5">пишите нам<br><a href="http://azovsky.ru/mail-us/"
                                                                         title="Наш Имейл"><span>mail&#64;azovsky&#46;ru</span></a>
                </div>
            </div>
            <div class="row menurow">
                <nav class="col-md-12 text-right">
                    <ul>
                        <li><a href="index.html" title="Главная">Главная</a></li>
                        <li class="deeper"><a href="http://azovsky.ru/vibor-pansionata/" title="Выбор пансионата">Выбор
                            пансионата <img src="images/str.png" alt=""></a>
                            <ul class="subul">
                                <li><a href="http://azovsky.ru/vibor-pansionata/" title="О нас">О нас</a></li>
                                <li id="subaz"><a href="http://azovsky.ru/azovsky/main/" title="Пансионат Азовский">Пансионат
                                    Азовский <span>&#9658;</span></a>
                                    <ul class="submenu" style="display:none;" id="subazul">
                                        <li><a href="http://azovsky.ru/azovsky/main/" title="Пансионат Азовский">Пансионат
                                            Азовский</a></li>
                                        <li><a href="http://azovsky.ru/rannee-bronirovanie-krym/"
                                               title="Отдых лето 2017">Отдых лето 2017</a></li>
                                        <li><a href="http://azovsky.ru/azovsky/azovskiy-about/" title="О пансионате">О
                                            пансионате</a></li>
                                        <li><a href="http://azovsky.ru/specpredlojenia-dnia/" title="Спецпредложения">Спецпредложения</a>
                                        </li>
                                        <li><a href="http://azovsky.ru/azovsky/azovsky-scheme/"
                                               title="Схема пансионата">Схема пансионата</a></li>
                                        <li><a href="http://azovsky.ru/azovsky/azovsky-may-crimea/" title="Отдых в мае">Отдых
                                            в мае</a></li>
                                        <li><a href="http://azovsky.ru/azovsky/azovsky-promo/" title="Акции">Акции</a>
                                        </li>
                                        <li><a href="http://azovsky.ru/azovsky/azovskiy-rooms/"
                                               title="Номера">Номера</a></li>
                                        <li><a href="http://azovsky.ru/azovsky/price/" title="Цены">Цены</a></li>
                                        <li><a href="http://azovsky.ru/azovsky/photo/"
                                               title="Фотогалерея">Фотогалерея</a></li>
                                        <li><a href="http://azovsky.ru/otdyh-s-detmi-v-krymu/" title="Отдых с детьми">Отдых
                                            с детьми</a></li>
                                        <li><a href="http://azovsky.ru/azovsky/menu-stolovoi-v-azovskom/"
                                               title="Меню столовой">Меню столовой</a></li>
                                        <li><a href="http://azovsky.ru/animation/" title="Анимация">Анимация</a></li>
                                        <li><a href="http://azovsky.ru/azovsky/azovskiy-festivals/"
                                               title="События и фестивали">События и фестивали</a></li>
                                        <li><a href="http://azovsky.ru/azovsky/azovsky-review/"
                                               title="Отзывы">Отзывы</a></li>
                                        <li><a href="http://azovsky.ru/azovsky/azovskiy-sport/" title="Спорт и отдых">Спорт
                                            и отдых</a></li>
                                        <li><a href="http://azovsky.ru/spa/" title="SPA">SPA</a></li>
                                        <li><a href="http://azovsky.ru/excursions/" title="Экскурсии">Экскурсии</a></li>
                                        <li><a href="http://azovsky.ru/payment/" title="Как купить">Как купить</a></li>
                                        <li><a href="http://azovsky.ru/road/" title="Как добраться">Как добраться</a>
                                        </li>
                                        <li><a href="http://azovsky.ru/contact/" title="Контакты">Контакты</a></li>
                                        <li><a href="http://azovsky.ru/blog/" title="Блог">Блог</a></li>
                                    </ul>
                                </li>
                                <li id="subazlnd"><a href="http://azovsky.ru/azovland/main/" title="Пансионат АзовЛенд">Пансионат
                                    АзовЛенд <span>&#9658;</span></a>
                                    <ul class="submenu" style="display:none;" id="subazlndul">
                                        <li><a href="http://azovsky.ru/azovland/main/" title="Пансионат АзовЛенд">Пансионат
                                            АзовЛенд</a></li>
                                        <li><a href="http://azovsky.ru/azovland/azovland-about/" title="О пансионате">О
                                            пансионате</a></li>
                                        <li><a href="http://azovsky.ru/azovland/shema_pansionata_azovlend/"
                                               title="Схема пансионата">Схема пансионата</a></li>
                                        <li><a href="http://azovsky.ru/azovland/azovland-promo/" title="Акции">Акции</a>
                                        </li>
                                        <li><a href="http://azovsky.ru/specpredlojenia-dnia/" title="Спецпредложения">Спецпредложения</a>
                                        </li>
                                        <li><a href="http://azovsky.ru/azovland/tipi-nomerov/" title="Номера">Номера</a>
                                        </li>
                                        <li><a href="http://azovsky.ru/rannee-bronirovanie-krym/"
                                               title="Отдых лето 2017">Отдых лето 2017</a></li>
                                        <li><a href="http://azovsky.ru/azovland/price/" title="Цены">Цены</a></li>
                                        <li><a href="http://azovsky.ru/azovland/photo/"
                                               title="Фотогалерея">Фотогалерея</a></li>
                                        <li><a href="http://azovsky.ru/otdyh-s-detmi-v-krymu/" title="Отдых с детьми">Отдых
                                            с детьми</a></li>
                                        <li><a href="http://azovsky.ru/azovland/menu-shvedskogo-stola-v-azovlende/"
                                               title="Меню шведского стола">Меню шведского стола</a></li>
                                        <li><a href="http://azovsky.ru/animation/" title="Анимация">Анимация</a></li>
                                        <li><a href="http://azovsky.ru/azovland/azovland-festivals/"
                                               title="События и фестивали">События и фестивали</a></li>
                                        <li><a href="http://azovsky.ru/azovland/review-azovland/"
                                               title="Отзывы">Отзывы</a></li>
                                        <li><a href="http://azovsky.ru/azovland/azovland-sport/" title="Спорт и отдых">Спорт
                                            и отдых</a></li>
                                        <li><a href="http://azovsky.ru/azovland/foto-konkurs-2015-v-park-otele-rio/"
                                               title="Фотоконкурсы">Фотоконкурсы</a></li>
                                        <li><a href="http://azovsky.ru/azovland/spa/" title="SPA">SPA</a></li>
                                        <li><a href="http://azovsky.ru/excursions/" title="Экскурсии">Экскурсии</a></li>
                                        <li><a href="http://azovsky.ru/payment/" title="Как купить">Как купить</a></li>
                                        <li><a href="http://azovsky.ru/road/" title="Как добраться">Как добраться</a>
                                        </li>
                                        <li><a href="http://azovsky.ru/contact/" title="Контакты">Контакты</a></li>
                                        <li><a href="http://azovsky.ru/blog/" title="Блог">Блог</a></li>
                                    </ul>
                                </li>
                                <li id="subrio"><a href="http://azovsky.ru/riopark/main/" title="Парк-отель РИО">Парк-отель
                                    РИО <span>&#9658;</span></a>
                                    <ul class="submenu" style="display:none;" id="subrioul">
                                        <li><a href="http://azovsky.ru/riopark/main/" title="Парк-отель РИО">Парк-отель
                                            РИО</a></li>
                                        <li><a href="http://azovsky.ru/specpredlojenia-dnia/" title="Спецпредложения">Спецпредложения</a>
                                        </li>
                                        <li><a href="http://azovsky.ru/riopark/riopark-about/" title="Об отеле">Об
                                            отеле</a></li>
                                        <li><a href="http://azovsky.ru/riopark/shema-otela-riopark/"
                                               title="Схема территории">Схема территории</a></li>
                                        <li><a href="http://azovsky.ru/riopark/riopark-promo/" title="Акции">Акции</a>
                                        </li>
                                        <li><a href="http://azovsky.ru/riopark/riopark-rooms/" title="Номера">Номера</a>
                                        </li>
                                        <li><a href="http://azovsky.ru/riopark/price/" title="Цены">Цены</a></li>
                                        <li><a href="http://azovsky.ru/riopark/photo/"
                                               title="Фотогалерея">Фотогалерея</a></li>
                                        <li><a href="http://azovsky.ru/riopark/semeinyi-otdyh-rio/"
                                               title="Отдых с детьми">Отдых с детьми</a></li>
                                        <li><a href="http://azovsky.ru/riopark/menu-stolovoi-v-riopark/"
                                               title="Меню шведского стола">Меню шведского стола</a></li>
                                        <li><a href="http://azovsky.ru/animation/" title="Анимация">Анимация</a></li>
                                        <li><a href="http://azovsky.ru/riopark/rio-festivals/"
                                               title="События и фестивали">События и фестивали</a></li>
                                        <li><a href="http://azovsky.ru/riopark/otzivi-o-riopark/"
                                               title="Отзывы">Отзывы</a></li>
                                        <li><a href="http://azovsky.ru/riopark/foto-konkurs-2015-v-park-otele-rio/"
                                               title="Фотоконкурсы">Фотоконкурсы</a></li>
                                        <li><a href="http://azovsky.ru/riopark/aktivnyi-otdyh-sport-rio/"
                                               title="Спорт и отдых">Спорт и отдых</a></li>
                                        <li><a href="http://azovsky.ru/riopark/spa/" title="SPA">SPA</a></li>
                                        <li><a href="http://azovsky.ru/excursions/" title="Экскурсии">Экскурсии</a></li>
                                        <li><a href="http://azovsky.ru/payment/" title="Как купить">Как купить</a></li>
                                        <li><a href="http://azovsky.ru/road/" title="Как добраться">Как добраться</a>
                                        </li>
                                        <li><a href="http://azovsky.ru/contact/" title="Контакты">Контакты</a></li>
                                        <li><a href="http://azovsky.ru/blog/" title="Блог">Блог</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li><a href="http://azovsky.ru/payment/" title="Как купить">Как купить</a></li>
                        <li class="deeper"><a href="http://azovsky.ru/road/" title="Как добраться">Как добраться <img
                                src="images/str.png" alt=""></a>
                            <ul class="subul">
                                <li><a href="http://azovsky.ru/road/" title="Варианты проезда">Проезд</a></li>
                                <li><a href="http://azovsky.ru/transfer/" title="Трансфер">Трансфер</a></li>
                                <li><a href="http://azovsky.ru/crimea-map/" title="На карте крыма">Карта крыма</a></li>
                            </ul>
                        </li>
                        <li class="deeper" id="conts"><a href="http://azovsky.ru/contact/" title="Контакты">Контакты
                            <img src="images/str.png" alt=""></a>
                            <ul class="subul" id="subconts">
                                <li><a href="http://azovsky.ru/contact/" title="Контакты">Контакты</a></li>
                                <li><a href="http://azovsky.ru/contact/agencys/" title="сотрудничество с агентствами">Агентствам</a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                    <div class="clear"></div>
                </nav>
            </div>
        </div>
    </div>
</header>
<section>
    <div class="container">
        <div class="col-md-3 mpagenopadding" style="padding-right:0px;padding-left:0px;">
            <div class="formmainpage">
                <link rel="stylesheet" type="text/css"
                      href="http://azovsky.ru/services/mb-search/vendor/chosen_v1.0.0/chosen.css">
                <link rel="stylesheet" type="text/css"
                      href="http://azovsky.ru/services/mb-search/vendor/bootstrap-datepicker-master/css/datepicker.css">
                <link rel="stylesheet" type="text/css"
                      href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.1/css/jquery.dataTables.css">


                <!--<form class="bronirovanie"
                      action="http://azovsky.ru/services/mb-search/"
                      id="formFilters" method="GET">
                    <div class="hinform h2">Расчёт стоимости</div>
                    <p>Пансионат</p>
                    <select name='hotel' class="frst" id="hotel"></select>
                    <p>Тип номера</p>
                    <select name='roomType' class="input-medium"
                            id="roomType"></select>
                    <div class="row sbrw">
                        <div class="col-md-6 col-xs-12">
                            <p>Заезд</p>
                            <input type="text" required="required" name='begin'
                                   class="frst" id="dateBegin" value='27.09.2016'/>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <p>Выезд</p>
                            <input type="text" required="required" name='end'
                                   class="input-small" id="dateEnd"
                                   value='27.09.2016'/>
                        </div>
                    </div>
                    <div class="row sbrw sbrwtwo">
                        <div class="col-md-6 col-xs-12">
                            <p>Взрослых</p>
                            <input type="number" name="adults" min="1" max="6"
                                   step="1" value="2" class="frst" id="adults">
                            <div class="aznew_bron_adults_all">
                                <div class="aznew_bron_adults" rel="1"></div>
                                <div class="aznew_bron_adults" rel="2"></div>
                                <div class="aznew_bron_adults" rel="3"></div>
                                <div class="aznew_bron_adults" rel="4"></div>
                                <div class="aznew_bron_adults" rel="5"></div>
                                <div class="aznew_bron_adults" rel="6"></div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <p>Детей</p>
                            <input type="number" name="childrens" min="0" max="5"
                                   step="1" value="0" id="childrens">
                            <div id="aznew_childs_age_block" class="hide">
                                <div id="aznew_childs_age"
                                     title="Указать возраст детей"></div>
                                <span id="aznew_count_child"
                                      style="display:none;"></span>
                                <div class="aznew_bron_childrens_all hide">
                                    <div class="aznew_bron_childrens" rel="1"></div>
                                    <div class="aznew_bron_childrens" rel="2"></div>
                                    <div class="aznew_bron_childrens" rel="3"></div>
                                    <div class="aznew_bron_childrens" rel="4"></div>
                                    <div class="aznew_bron_childrens" rel="5"></div>
                                </div>
                                <div id="aznew_childs_age_area hide">
                                    <div id="aznew_childs_age_close"></div>
                                    <div id="aznew_childs_age_arr"></div>
                                    <div style="color:#fff;font-size:10px;font-weight:bold;">
                                        Возраста детей
                                    </div>
                                    <div id="aznew_childs_age_ages">
                                        <input type="text" min="0" max="99" step="1"
                                               name='age_1' id="age_1" value=''
                                               placeholder='' maxlength="2"
                                               class="input-mini">
                                        <input type="text" min="0" max="99" step="1"
                                               name='age_2' id="age_2" value=''
                                               placeholder='' maxlength="2"
                                               class="input-mini">
                                        <input type="text" min="0" max="99" step="1"
                                               name='age_3' id="age_3" value=''
                                               placeholder='' maxlength="2"
                                               class="input-mini">
                                        <input type="text" min="0" max="99" step="1"
                                               name='age_4' id="age_4" value=''
                                               placeholder='' maxlength="2"
                                               class="input-mini">
                                        <input type="text" min="0" max="99" step="1"
                                               name='age_5' id="age_5" value=''
                                               placeholder='' maxlength="2"
                                               class="input-mini">
                                    </div>
                                    <div style="color:#666;font-size:10px;">Полных
                                        лет на дату заезда
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <p>Скидка</p>
                    <select name='discount' class="input-medium" id="discount"
                            data-placeholder="Сделайте выбор"></select>

                    <input type="button" name="" value="Рассчитать"
                           onclick="mbSubmitFormFilters()" id="btn-search">
                    <div class="hinform h2" style="padding:16px 0 10px;"><a
                            href="http://azovsky-billing.com/pay/"
                            target="_blank"><img src="images/vzmc.png"
                                                 style="margin-right:10px;height:30px;">Внести
                        оплату Online</a></div>

                </form>-->



                <script src="http://azovsky.ru/services/mb-search/js/jquery.maskedinput.min.js"></script>
                <script src="http://azovsky.ru/services/mb-search/vendor/chosen_v1.0.0/chosen.jquery.js"></script>
                <script src="http://azovsky.ru/services/mb-search/vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.js"></script>
                <script src="http://azovsky.ru/services/mb-search/vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.utf.js"></script>
                <script src="http://azovsky.ru/services/mb-search/js/jquery.dataTables.min.js"></script>
                <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
                <script src="../../moment/moment.js"></script>
                <script src="../../bootstrap-daterangepicker/daterangepicker.js"></script>


                <?php
                $content = file_get_contents('http://az.maxibooking.ru/online_booking/form');
                echo $content;
                ?>
                <script>
                    mb_host = "azovsky.ru";
                    mb_datebegin_def = "27.09.2016";
                    mb_dateend_def = "27.09.2016";

                    $(function () {
                        filters = {
                            "hotel": {
                                "1": "\u041f\u0430\u043d\u0441\u0438\u043e\u043d\u0430\u0442 \u0410\u0437\u043e\u0432\u0441\u043a\u0438\u0439",
                                "2": "\u041f\u0430\u043d\u0441\u0438\u043e\u043d\u0430\u0442 \u0410\u0437\u043e\u0432\u041b\u0435\u043d\u0434",
                                "3": "\u041f\u0430\u0440\u043a-\u043e\u0442\u0435\u043b\u044c \u0420\u0418\u041e"
                            },
                            "roomType": {
                                "1": {
                                    "4": "\u0421\u0442\u0430\u043d\u0434\u0430\u0440\u0442\u043d\u044b\u0435 (max 2 \u0447\u0435\u043b)",
                                    "34": "\u041d\u043e\u043c\u0435\u0440\u0430 \u0441 \u043a\u043e\u043d\u0434\u0438\u0446\u0438\u043e\u043d\u0435\u0440\u0430\u043c\u0438 (max 5 \u0447\u0435\u043b)",
                                    "64": "\u041a\u043e\u043c\u0444\u043e\u0440\u0442 \u043f\u043b\u044e\u0441 (max 6 \u0447\u0435\u043b)",
                                    "27": "\u0414\u043e\u043c\u0438\u043a\u0438 \u0441 \u0432\u0435\u0440\u0430\u043d\u0434\u0430\u043c\u0438 (max 3 \u0447\u0435\u043b)",
                                    "38": "\u041d\u043e\u043c\u0435\u0440\u0430 \u0441 \u043a\u043e\u043d\u0434\u0438\u0446\u0438\u043e\u043d\u0435\u0440\u0430\u043c\u0438 2-\u043a\u043e\u043c\u043d\u0430\u0442\u043d\u044b\u0435 3-\u0445 \u043c\u0435\u0441\u0442\u043d\u044b\u0435",
                                    "39": "\u041d\u043e\u043c\u0435\u0440\u0430 \u0441 \u043a\u043e\u043d\u0434\u0438\u0446\u0438\u043e\u043d\u0435\u0440\u0430\u043c\u0438 2-\u043a\u043e\u043c\u043d\u0430\u0442\u043d\u044b\u0435 4-\u0445 \u043c\u0435\u0441\u0442\u043d\u044b\u0435",
                                    "33": "\u0421\u0435\u043c\u0435\u0439\u043d\u044b\u0435 \u0430\u043f\u0430\u0440\u0442\u0430\u043c\u0435\u043d\u0442\u044b 2-\u043a\u043e\u043c\u043d\u0430\u0442\u043d\u044b\u0435 5-\u0442\u0438 \u043c\u0435\u0441\u0442\u043d\u044b\u0435"
                                },
                                "2": {
                                    "40": "\u0421\u0442\u0430\u043d\u0434\u0430\u0440\u0442 \u0432 \u0434\u043e\u043c\u0438\u043a\u0430\u0445",
                                    "41": "\u041a\u043e\u043c\u0444\u043e\u0440\u0442 \u0432 \u043a\u043e\u0440\u043f\u0443\u0441\u0430\u0445",
                                    "42": "\u041d\u043e\u043c\u0435\u0440\u0430 \u043a\u043e\u043c\u0444\u043e\u0440\u0442 \u0432 \u0434\u043e\u043c\u0438\u043a\u0430\u0445",
                                    "52": "\u041a\u043e\u043c\u0444\u043e\u0440\u0442 \u043f\u043b\u044e\u0441",
                                    "79": "\u0410\u0437\u043e\u0432\u041b\u0435\u043d\u0434. \u041a\u043e\u043c\u0444\u043e\u0440\u0442 \u043f\u043b\u044e\u0441 \u043f\u0430\u043d\u043e\u0440\u0430\u043c\u043d\u044b\u0439 \u0432\u0438\u0434 \u043d\u0430 \u043c\u043e\u0440\u0435",
                                    "53": "\u0421\u0435\u043c\u0435\u0439\u043d\u044b\u0435 \u043d\u043e\u043c\u0435\u0440\u0430 \u043f\u0430\u043d\u043e\u0440\u0430\u043c\u043d\u044b\u0439 \u0432\u0438\u0434 \u043d\u0430 \u043c\u043e\u0440\u0435",
                                    "54": "\u0421\u0435\u043c\u0435\u0439\u043d\u044b\u0435 \u0430\u043f\u0430\u0440\u0442\u0430\u043c\u0435\u043d\u0442\u044b 2-\u043a\u043e\u043c\u043d\u0430\u0442\u043d\u044b\u0435 \u043f\u0430\u043d\u043e\u0440\u0430\u043c\u043d\u044b\u0439 \u0432\u0438\u0434 \u043d\u0430 \u043c\u043e\u0440\u0435",
                                    "83": " \u041d\u043e\u043c\u0435\u0440\u0430 \u0441 \u043a\u043e\u043d\u0434\u0438\u0446\u0438\u043e\u043d\u0435\u0440\u0430\u043c\u0438"
                                },
                                "3": {
                                    "58": "\u041d\u043e\u043c\u0435\u0440\u0430 \u0441\u0442\u0430\u043d\u0434\u0430\u0440\u0442 \u0432 \u044d\u043a\u043e-\u0434\u043e\u043c\u0438\u043a\u0430\u0445 (max 3 \u0447\u0435\u043b)",
                                    "57": "\u041a\u043e\u043c\u0444\u043e\u0440\u0442 (max 3 \u0447\u0435\u043b)",
                                    "77": "\u041a\u043e\u043c\u0444\u043e\u0440\u0442 \u0441 \u0431\u0430\u043b\u043a\u043e\u043d\u043e\u043c (max 3\u0447\u0435\u043b). \u041f\u0430\u043d\u043e\u0440\u0430\u043c\u043d\u044b\u0439 \u0432\u0438\u0434 \u043d\u0430 \u043c\u043e\u0440\u0435",
                                    "70": "\u0421\u0435\u043c\u0435\u0439\u043d\u044b\u0435 \u0441 \u0432\u0435\u0440\u0430\u043d\u0434\u043e\u0439 (max 4 \u0447\u0435\u043b)",
                                    "74": "\u041d\u043e\u043c\u0435\u0440\u0430 2-\u043a\u043e\u043c\u043d\u0430\u0442\u043d\u044b\u0435 \u0441 \u043f\u0430\u043d\u043e\u0440\u0430\u043c\u043d\u044b\u043c \u0432\u0438\u0434\u043e\u043c \u043d\u0430 \u043c\u043e\u0440\u0435 (\u0434\u043e 6 \u0447\u0435\u043b)"
                                },
                                "4": {
                                    "60": "\u041d\u043e\u043c\u0435\u0440\u0430 \u043a\u043e\u043c\u0444\u043e\u0440\u0442 \u0432 \u043a\u043e\u0442\u0442\u0435\u0434\u0436\u0430\u0445",
                                    "63": "\u041d\u043e\u043c\u0435\u0440\u0430 \u0441\u0435\u043c\u0435\u0439\u043d\u044b\u0435 2-\u043a\u043e\u043c\u043d\u0430\u0442\u043d\u044b\u0435 \u0432 \u043a\u043e\u0442\u0442\u0435\u0434\u0436\u0430\u0445"
                                }
                            },
                            "discount": {
                                "1": {
                                    "0#13": "\u0410\u043a\u0446\u0438\u0438 \u0432 \u0441\u0435\u043d\u0442\u044f\u0431\u0440\u0435",
                                    "25#42": "\u0420\u0430\u043d\u043d\u0435\u0435 \u0431\u0440\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435 2017 (25%) \u043f\u0440\u0438 100% \u043e\u043f\u043b\u0430\u0442\u0435 (25% \u0441\u043a\u0438\u0434\u043a\u0430)",
                                    "9#35": "\u0420\u0430\u043d\u043d\u0435\u0435 \u0431\u0440\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435 2016 (9%) \u043f\u0440\u0438 50% \u043e\u043f\u043b\u0430\u0442\u0435 (9% \u0441\u043a\u0438\u0434\u043a\u0430)",
                                    "14#37": "\u0420\u0430\u043d\u043d\u0435\u0435 \u0431\u0440\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435 2016 (14%) \u043f\u0440\u0438 100% \u043e\u043f\u043b\u0430\u0442\u0435 (14% \u0441\u043a\u0438\u0434\u043a\u0430)",
                                    "22#43": "\u0420\u0430\u043d\u043d\u0435\u0435 \u0431\u0440\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435 2017 (22%) \u043f\u0440\u0438 50% \u043e\u043f\u043b\u0430\u0442\u0435 (22% \u0441\u043a\u0438\u0434\u043a\u0430)"
                                },
                                "2": {
                                    "0#13": "\u0410\u043a\u0446\u0438\u0438 \u0432 \u0441\u0435\u043d\u0442\u044f\u0431\u0440\u0435",
                                    "9#35": "\u0420\u0430\u043d\u043d\u0435\u0435 \u0431\u0440\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435 2016 (9%) \u043f\u0440\u0438 50% \u043e\u043f\u043b\u0430\u0442\u0435 (9% \u0441\u043a\u0438\u0434\u043a\u0430)",
                                    "14#37": "\u0420\u0430\u043d\u043d\u0435\u0435 \u0431\u0440\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435 2016 (14%) \u043f\u0440\u0438 100% \u043e\u043f\u043b\u0430\u0442\u0435 (14% \u0441\u043a\u0438\u0434\u043a\u0430)",
                                    "20#46": "\u0420\u0430\u043d\u043d\u0435\u0435 \u0431\u0440\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435 2017 (20%) \u043f\u0440\u0438 100% \u043e\u043f\u043b\u0430\u0442\u0435 (20% \u0441\u043a\u0438\u0434\u043a\u0430)",
                                    "18#47": "\u0420\u0430\u043d\u043d\u0435\u0435 \u0431\u0440\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435 2017 (18%) \u043f\u0440\u0438 50% \u043e\u043f\u043b\u0430\u0442\u0435 (18% \u0441\u043a\u0438\u0434\u043a\u0430)"
                                },
                                "3": {
                                    "18#39": "\u0420\u0430\u043d\u043d\u0435\u0435 \u0431\u0440\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435 2016 (18%) (18% \u0441\u043a\u0438\u0434\u043a\u0430)",
                                    "14#41": "\u0420\u0430\u043d\u043d\u0435\u0435 \u0431\u0440\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435 2016 (14%) \u043f\u0440\u0438 50% \u043e\u043f\u043b\u0430\u0442\u0435 (14% \u0441\u043a\u0438\u0434\u043a\u0430)"
                                }
                            },
                            "dates": {
                                "1": {
                                    "in": ["2016-09-27", "2016-09-28", "2016-09-29", "2016-09-30", "2016-10-01", "2016-10-02", "2016-10-03", "2016-10-04", "2016-10-05", "2016-10-06", "2016-10-07", "2016-10-08", "2016-10-09", "2016-10-10", "2016-10-11", "2016-10-12", "2016-10-13", "2016-10-14", "2016-10-15", "2017-04-28", "2017-04-29", "2017-04-30", "2017-05-01", "2017-05-02", "2017-05-03", "2017-05-04", "2017-05-05", "2017-05-06", "2017-05-07", "2017-05-08", "2017-05-09", "2017-05-10", "2017-05-11", "2017-05-12", "2017-05-13", "2017-05-14", "2017-05-15", "2017-05-16", "2017-05-17", "2017-05-18", "2017-05-19", "2017-05-20", "2017-05-21", "2017-05-22", "2017-05-23", "2017-05-24", "2017-05-25", "2017-05-26", "2017-05-27", "2017-05-28", "2017-05-29", "2017-05-30", "2017-05-31", "2017-06-01", "2017-06-02", "2017-06-03", "2017-06-04", "2017-06-05", "2017-06-06", "2017-06-07", "2017-06-08", "2017-06-09", "2017-06-10", "2017-06-11", "2017-06-12", "2017-06-13", "2017-06-14", "2017-06-17", "2017-06-19", "2017-06-21", "2017-06-24", "2017-06-26", "2017-06-28", "2017-07-01", "2017-07-03", "2017-07-05", "2017-07-08", "2017-07-10", "2017-07-12", "2017-07-15", "2017-07-17", "2017-07-19", "2017-07-22", "2017-07-24", "2017-07-26", "2017-07-29", "2017-07-31", "2017-08-02", "2017-08-05", "2017-08-07", "2017-08-09", "2017-08-12", "2017-08-14", "2017-08-16", "2017-08-19", "2017-08-21", "2017-08-23", "2017-08-26", "2017-08-27", "2017-08-28", "2017-08-29", "2017-08-30", "2017-08-31", "2017-09-01", "2017-09-02", "2017-09-03", "2017-09-04", "2017-09-05", "2017-09-06", "2017-09-07", "2017-09-08", "2017-09-09", "2017-09-10", "2017-09-11", "2017-09-12", "2017-09-13", "2017-09-14", "2017-09-15", "2017-09-16", "2017-09-17", "2017-09-18", "2017-09-19", "2017-09-20", "2017-09-21", "2017-09-22", "2017-09-23", "2017-09-24", "2017-09-25", "2017-09-26", "2017-09-27", "2017-09-28", "2017-09-29", "2017-09-30", "2017-10-01"],
                                    "out": ["2016-09-27", "2016-09-28", "2016-09-29", "2016-09-30", "2016-10-01", "2016-10-02", "2016-10-03", "2016-10-04", "2016-10-05", "2016-10-06", "2016-10-07", "2016-10-08", "2016-10-09", "2016-10-10", "2016-10-11", "2016-10-12", "2016-10-13", "2016-10-14", "2016-10-15", "2017-04-28", "2017-04-29", "2017-04-30", "2017-05-01", "2017-05-02", "2017-05-03", "2017-05-04", "2017-05-05", "2017-05-06", "2017-05-07", "2017-05-08", "2017-05-09", "2017-05-10", "2017-05-11", "2017-05-12", "2017-05-13", "2017-05-14", "2017-05-15", "2017-05-16", "2017-05-17", "2017-05-18", "2017-05-19", "2017-05-20", "2017-05-21", "2017-05-22", "2017-05-23", "2017-05-24", "2017-05-25", "2017-05-26", "2017-05-27", "2017-05-28", "2017-05-29", "2017-05-30", "2017-05-31", "2017-06-01", "2017-06-02", "2017-06-03", "2017-06-04", "2017-06-05", "2017-06-06", "2017-06-07", "2017-06-08", "2017-06-09", "2017-06-10", "2017-06-11", "2017-06-12", "2017-06-13", "2017-06-14", "2017-06-17", "2017-06-19", "2017-06-21", "2017-06-24", "2017-06-26", "2017-06-28", "2017-07-01", "2017-07-03", "2017-07-05", "2017-07-08", "2017-07-10", "2017-07-12", "2017-07-15", "2017-07-17", "2017-07-19", "2017-07-22", "2017-07-24", "2017-07-26", "2017-07-29", "2017-07-31", "2017-08-02", "2017-08-05", "2017-08-07", "2017-08-09", "2017-08-12", "2017-08-14", "2017-08-16", "2017-08-19", "2017-08-21", "2017-08-23", "2017-08-26", "2017-08-27", "2017-08-28", "2017-08-29", "2017-08-30", "2017-08-31", "2017-09-01", "2017-09-02", "2017-09-03", "2017-09-04", "2017-09-05", "2017-09-06", "2017-09-07", "2017-09-08", "2017-09-09", "2017-09-10", "2017-09-11", "2017-09-12", "2017-09-13", "2017-09-14", "2017-09-15", "2017-09-16", "2017-09-17", "2017-09-18", "2017-09-19", "2017-09-20", "2017-09-21", "2017-09-22", "2017-09-23", "2017-09-24", "2017-09-25", "2017-09-26", "2017-09-27", "2017-09-28", "2017-09-29", "2017-09-30", "2017-10-01"]
                                },
                                "2": {
                                    "in": ["2017-05-27", "2017-05-28", "2017-05-29", "2017-05-30", "2017-05-31", "2017-06-01", "2017-06-02", "2017-06-03", "2017-06-04", "2017-06-05", "2017-06-06", "2017-06-07", "2017-06-08", "2017-06-09", "2017-06-10", "2017-06-11", "2017-06-12", "2017-06-13", "2017-06-14", "2017-06-17", "2017-06-19", "2017-06-21", "2017-06-24", "2017-06-26", "2017-06-28", "2017-07-01", "2017-07-03", "2017-07-05", "2017-07-08", "2017-07-10", "2017-07-12", "2017-07-15", "2017-07-17", "2017-07-19", "2017-07-22", "2017-07-24", "2017-07-26", "2017-07-29", "2017-07-31", "2017-08-02", "2017-08-05", "2017-08-07", "2017-08-09", "2017-08-12", "2017-08-14", "2017-08-16", "2017-08-19", "2017-08-21", "2017-08-23", "2017-08-26", "2017-08-27", "2017-08-28", "2017-08-29", "2017-08-30", "2017-08-31", "2017-09-01", "2017-09-02", "2017-09-03", "2017-09-04", "2017-09-05", "2017-09-06", "2017-09-07", "2017-09-08", "2017-09-09", "2017-09-10", "2017-09-11", "2017-09-12", "2017-09-13", "2017-09-14", "2017-09-15", "2017-09-16", "2017-09-17"],
                                    "out": ["2017-05-27", "2017-05-28", "2017-05-29", "2017-05-30", "2017-05-31", "2017-06-01", "2017-06-02", "2017-06-03", "2017-06-04", "2017-06-05", "2017-06-06", "2017-06-07", "2017-06-08", "2017-06-09", "2017-06-10", "2017-06-11", "2017-06-12", "2017-06-13", "2017-06-14", "2017-06-17", "2017-06-19", "2017-06-21", "2017-06-24", "2017-06-26", "2017-06-28", "2017-07-01", "2017-07-03", "2017-07-05", "2017-07-08", "2017-07-10", "2017-07-12", "2017-07-15", "2017-07-17", "2017-07-19", "2017-07-22", "2017-07-24", "2017-07-26", "2017-07-29", "2017-07-31", "2017-08-02", "2017-08-05", "2017-08-07", "2017-08-09", "2017-08-12", "2017-08-14", "2017-08-16", "2017-08-19", "2017-08-21", "2017-08-23", "2017-08-26", "2017-08-27", "2017-08-28", "2017-08-29", "2017-08-30", "2017-08-31", "2017-09-01", "2017-09-02", "2017-09-03", "2017-09-04", "2017-09-05", "2017-09-06", "2017-09-07", "2017-09-08", "2017-09-09", "2017-09-10", "2017-09-11", "2017-09-12", "2017-09-13", "2017-09-14", "2017-09-15", "2017-09-16", "2017-09-17"]
                                }
                            }
                        };
                    });    </script>


            </div>
        </div>
        <div class="col-md-9 col-xs-12" style="padding-right:0px;padding-left:0px;">
            <div id="carousel-example-generic" class="carousel price_slider slide" data-ride="carousel">
                <!-- Indicators -->
                <ol class="carousel-indicators">
                    <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="1"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="2"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="3"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="4"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="5"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="6"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="7"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="8"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="9"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="10"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="11"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="12"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="13"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="14"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="15"></li>

                </ol>

                <!--Wrapper for slides-->
                <div class="carousel-inner" role="listbox">
                    <div class="season">сезон<br><span>2017</span></div>

                    <div class="item active">
                        <a href="http://azovsky.ru/rannee-bronirovanie-krym/"><img src="images/slider-2017.jpg"
                                                                                   alt="Раннее бронирование - скидки до 25%"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>


                    <div class="item">
                        <a href="http://azovsky.ru/azovland/menu-shvedskogo-stola-v-azovlende/"><img
                                src="images/shv-stol-v-azl-sl.jpg" alt="Шведский стол в пансионате Азовленд 2017"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>


                    <!--<div class="item">
                      <a href="/news/otdih-v-pansionatah-kurortnoi-seti-azovsky-teper-i-s-perelyotom/"><img src="/images/az/07ks-03.jpg" alt="Отдых в пансионатах Курортной сети Азовский теперь и с перелетом"></a>
                     <div class="carousel-caption"></div>
                    </div>-->


                    <div class="item">
                        <a href="http://azovsky.ru/riopark/main/"><img src="images/az/07ks-05.jpg"
                                                                       alt="РИО-отдых в твоем стиле"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>

                    <div class="item">
                        <a href="http://azovsky.ru/riopark/main/"><img src="images/az/07ks-06.jpg"
                                                                       alt="РИО-белоснежный пляж"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>

                    <div class="item">
                        <a href="http://azovsky.ru/specpredlojenia-dnia/"><img src="images/az/07ks-07.jpg"
                                                                               alt="Водные горки в парк-отеле РИО"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>


                    <div class="item">
                        <a href="http://azovsky.ru/azovland/main/"><img src="images/az/07ks-08.jpg" alt="АзовЛенд"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>


                    <div class="item">
                        <a href="http://azovsky.ru/azovland/photo/akvapark-azovland/"><img src="images/az/07ks-09.jpg"
                                                                                           alt="Детский аквапарк в пансионате АзовЛенд"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>


                    <div class="item">
                        <a href="http://azovsky.ru/azovland/photo/vodnaya-gorka-azovland/"><img
                                src="images/az/07ks-10.jpg" alt="Водная горка в пансионате АзовЛенд"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>


                    <div class="item">
                        <a href="http://azovsky.ru/azovsky/azovskiy-akvapark-mirovogo-urovnya/"><img
                                src="images/az/07ks-11.jpg" alt="Пансионат Азовский с бесплатным аквапарком"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>

                    <div class="item">
                        <a href="http://azovsky.ru/azovsky/photo/animacia-azovsky/"><img src="images/az/07ks-12.jpg"
                                                                                         alt="Увлекательная анимация"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>

                    <div class="item">
                        <a href="http://azovsky.ru/azovsky/azovskiy-rooms/"><img src="images/az/07ks-13.jpg"
                                                                                 alt="Комфортные номера"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>

                    <div class="item">
                        <a href="http://azovsky.ru/azovsky/photo/bolshoy-tennis/"><img src="images/az/07ks-14.jpg"
                                                                                       alt="Теннисные корты"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>

                    <div class="item">
                        <a href="http://azovsky.ru/riopark/photo/multi-rio/"><img src="images/az/07ks-15.jpg"
                                                                                  alt="Спортивные площадки в РИО"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>

                    <div class="item">
                        <a href="http://azovsky.ru/vibor-pansionata/"><img src="images/az/07ks-16.jpg"
                                                                           alt="Чистейшее море"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>

                    <div class="item">
                        <a href="http://azovsky.ru/vibor-pansionata/"><img src="images/az/07ks-17.jpg"
                                                                           alt="Комфортный отдых"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>


                    <div class="item">
                        <a href="http://azovsky.ru/vibor-pansionata/"><img src="images/az/07ks-18.jpg"
                                                                           alt="Море эмоций"></a>
                        <!--<div class="carousel-caption"></div>-->
                    </div>


                </div>

            </div>
            <div class="menu">
                <nav>
                    <a href="http://azovsky.ru/promo/" title="Акции и скидки">
                        <div class="menuDiv darkred"><span>акции и скидки</span></div>
                    </a>
                    <a href="http://azovsky.ru/specpredlojenia-dnia/" title="Спецпредложения">
                        <div class="menuDiv yellow"><span>спецпредложения</span></div>
                    </a>
                    <a href="http://azovsky.ru/ceny-2017-na-otdyh-v-krymu/" title="Цены">
                        <div class="menuDiv green"><span>цены</span></div>
                    </a>
                    <a href="http://azovsky.ru/photo/" title="Фотогалереи">
                        <div class="menuDiv red"><span>Фотогалереи</span></div>
                    </a>
                    <a href="http://azovsky.ru/otdyh-s-detmi-v-krymu/" title="Отдых с детьми">
                        <div class="menuDiv blue"><span>отдых с детьми</span></div>
                    </a>
                    <a href="http://azovsky.ru/animation/" title="Анимация">
                        <div class="menuDiv orange"><span>анимация</span></div>
                    </a>
                    <a href="http://azovsky.ru/spa/" title="SPA">
                        <div class="menuDiv violet"><span>SPA</span></div>
                    </a>
                    <a href="http://azovsky.ru/sports/" title="Спорт">
                        <div class="menuDiv pink"><span>Спорт</span></div>
                    </a>
                    <a href="http://azovsky.ru/excursions/" title="Экскурсии">
                        <div class="menuDiv red last" style="width:99px;"><span>Экскурсии</span></div>
                    </a>
                    <div class="clear"></div>
                </nav>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="hotels row mainRow white">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="hotel"><a href="http://azovsky.ru/riopark/main/"><img src="images/hotels/rio-mainpage.jpg"
                                                                                  alt="Парк Отель Рио"></a></div>
                <div class="hotelTitle"><span>Парк Отель Рио</span><a href="http://azovsky.ru/riopark/main/"><img
                        src="images/Arrow1.png" class="arrow" alt="arrow"></a></div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="hotel"><a href="http://azovsky.ru/azovland/main/"><img
                        src="images/hotels/azovlandIMG_9106-1.jpg" alt="Пансионат АзовЛенд"></a></div>
                <div class="hotelTitle"><span>Пансионат АзовЛенд</span><a href="http://azovsky.ru/azovland/main/"><img
                        src="images/Arrow1.png" class="arrow" alt="arrow"></a></div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="hotel"><a href="http://azovsky.ru/azovsky/main/"><img
                        src="images/hotels/azovskyIMG_2596-2.jpg" alt="Пансионат Азовский"></a></div>
                <div class="hotelTitle"><span>Пансионат Азовский</span><a href="http://azovsky.ru/azovsky/main/"><img
                        src="images/Arrow1.png" class="arrow" alt="arrow"></a></div>
            </div>
        </div>
    </div>

</section>

<div class="container">
    <div class="colorline"></div>
    <div class="azov__line main row">
        <div class="mainText" style="padding:50px;">
            <h1>Отдых в Крыму на Азовском море</h1>
            <p>Курортная сеть «Азовский» предлагает <strong>отдых в Крыму на Азовском море без посредников</strong> для
                всей семьи по доступным ценам. Наши пансионаты пользуются заслуженной популярностью у отдыхающих. Это:
                «Азовский», «РИО» и «АзовЛенд».</p>
            <div class="slogan">Мы продаем путевки без посредников напрямую от объектов отдыха!</div>
            <p>Вы можете забронировать или приобрести путевку, как в нашем офисе, так и дистанционно. Нашими клиентами
                являются жители всех уголков России и даже стран СНГ. Если вы доверите нам заботы о качестве вашего
                отпуска, спешим заверить, что ваши каникулы будут незабываемыми. Яркие воспоминания об отдыхе в Крыму
                станут лучшим сувениром, который вы привезете с моря.</p>
            <p>Наряду с прекрасными климатическими условиями, живописными пейзажами, чистейшей песчаной береговой линией
                наши комфортабельные отели, пансионаты предлагают отдыхающим бассейны и аквапарки, спортивные и игровые
                площадки для детей и взрослых. Желающим поправить здоровье предоставляются разнообразные лечебные
                процедуры, для любителей путешествовать - познавательные экскурсии. Увлекательная анимационная программа
                и множество других интересных мероприятий займут ваших детей.</p>
            <p>Если вы предпочтете вариант "отдых в Крыму все включено", то всеми перечисленными услугами будете
                пользоваться в любое удобное для вас время, плюс прекрасное трехразовое питание из экологически чистых
                продуктов. </p>
            <div class="clear"><br></div>
        </div>
    </div>
</div>

<div class="container">
    <div class="colorline"></div>
    <div class="discounts row mainRow ">
        <div class="discountTitle">
            <div class="actionheader"><a class="promoa" href="http://azovsky.ru/promo/">Акции и скидки Курортной сети
                Азовский</a></div>
            <p>Не пропустите самые актуальные и горячие предложения на сегодня!</p>
        </div>
        <div class="discountBody">
            <!--<img class="arrowLeft arrow" src="images/arrow_left_discount.png">-->
            <div class="discountContainer">
                <div class="col-md-2 col-sm-2 col-xs-4">
                    <div class="discount"><a href="http://azovsky.ru/rannee-bronirovanie-krym/"><img
                            src="images/rbr-1.jpg" alt="Раннее бронирование"></a></div>
                    <div class="discountText pad"><a href="http://azovsky.ru/rannee-bronirovanie-krym/">"Раннее
                        бронирование"</a></div>
                </div>
                <div class="col-md-2 col-sm-2 col-xs-4">
                    <div class="discount"><a
                            href="https://avia.yandex.ru/search/result/?fromId=c213&fromName=%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0&toId=c146&toName=%D0%A1%D0%B8%D0%BC%D1%84%D0%B5%D1%80%D0%BE%D0%BF%D0%BE%D0%BB%D1%8C&when=2+%D0%B8%D1%8E%D0%BD&return_date=16+%D0%B8%D1%8E%D0%BD&lang=ru&oneway=2&adult_seats=1&children_seats=0&infant_seats=0&klass=economy"
                            target="_blank"><img src="images/promo/avia.jpg" alt="Дешёвые авиабилеты 2017"></a></div>
                    <div class="discountText pad"><a
                            href="https://avia.yandex.ru/search/result/?fromId=c213&fromName=%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0&toId=c146&toName=%D0%A1%D0%B8%D0%BC%D1%84%D0%B5%D1%80%D0%BE%D0%BF%D0%BE%D0%BB%D1%8C&when=2+%D0%B8%D1%8E%D0%BD&return_date=16+%D0%B8%D1%8E%D0%BD&lang=ru&oneway=2&adult_seats=1&children_seats=0&infant_seats=0&klass=economy"
                            target="_blank">Дешёвые авиабилеты 2017</a></div>
                </div>
                <div class="col-md-2 col-sm-2 col-xs-4">
                    <div class="discount"><a href="http://azovsky.ru/promo/promo-rebenok-besplatno/"><img
                            src="images/promo/rbp.jpg" alt="Ребенок бесплатно"></a></div>
                    <div class="discountText pad"><a href="http://azovsky.ru/promo/promo-rebenok-besplatno/">"Ребенок
                        бесплатно"</a></div>
                </div>
                <div class="col-md-2 col-sm-2 col-xs-4">
                    <div class="discount"><a href="http://azovsky.ru/promo/promo-odin-kak-gospodin/"><img
                            src="images/promo/odn.jpg" alt="Один как господин"></a></div>
                    <div class="discountText pad"><a href="http://azovsky.ru/promo/promo-odin-kak-gospodin/">"Один как
                        господин"</a></div>
                </div>
                <div class="col-md-2 col-sm-2 col-xs-4">
                    <div class="discount"><a href="http://azovsky.ru/promo/promo-vigodnie-exkursii/"><img
                            src="images/promo/exc.jpg" alt="Выгодные экскурсии"></a></div>
                    <div class="discountText pad"><a href="http://azovsky.ru/promo/promo-vigodnie-exkursii/">"Выгодные
                        экскурсии"</a></div>
                </div>
                <div class="col-md-2 col-sm-2 col-xs-4">
                    <div class="discount"><a href="http://azovsky.ru/promo/promo-skidki-detyam/"><img
                            src="images/promo/skd.jpg" alt="Скидки детям"></a></div>
                    <div class="discountText pad"><a href="http://azovsky.ru/promo/promo-skidki-detyam/">Скидки
                        детям</a></div>
                </div>
            </div>
            <!--<img class="arrow" src="images/arrow_right_discount.png">-->
            <div class="clear"></div>
        </div>
    </div>
</div>


<div class="container">
    <div class="colorline"></div>
    <div class="azov__line main row">
        <div class="mainText">
            <div>
                <div class="divh2">События курортной сети &laquo;Азовский&raquo;</div>
                <p style="text-align: center !important;">События, ставшие традиционными в пансионатах курортной
                    сети &laquo;Азовский&raquo;</p>
            </div>
        </div>
        <div class="mainRow row" style="padding: 10px 65px 0px;">
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="mainDiv"><a href="http://azovsky.ru/rannee-bronirovanie-krym/"><img
                        src="images/other/rannee.jpg" alt='Раннее бронирование 2017 Крым'></a></div>
                <div class="textrow"><a href="http://azovsky.ru/rannee-bronirovanie-krym/">Раннее бронирование на 2017 в
                    Крым</a></div>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="mainDiv"><a href="http://azovsky.ru/azovland/menu-shvedskogo-stola-v-azovlende/"><img
                        src="images/stolazl.jpg" alt='Новинка 2017! Шведский стол в АзовЛенде'></a></div>
                <div class="textrow"><a href="http://azovsky.ru/azovland/menu-shvedskogo-stola-v-azovlende/">Новинка
                    2017! Шведский стол в "АзовЛенде"</a></div>
            </div>

            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="mainDiv"><a href="http://azovsky.ru/fests/"><img src="images/festivals/candy-m.jpg"
                                                                             alt='фестивали курортной сети Азовский'></a>
                </div>
                <div class="textrow"><a href="http://azovsky.ru/fests/">События и фестивали курортной сети
                    «Азовский»</a></div>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="mainDiv">
                    <a href="http://azovsky.ru/azovsky/azovskiy-sport-deti-teens/"><img src="images/sport-main.jpg"
                                                                                        alt='Спортивные мероприятия'></a>
                </div>
                <div class="textrow">
                    <a href="http://azovsky.ru/azovsky/azovskiy-sport-deti-teens/">Спортивные сборы</a></div>
            </div>
        </div>
        <div class="mainRow row" style="padding: 40px 65px 50px;">
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="mainDiv">
                    <a href="http://azovsky.ru/azovland/azovland-festivals/"><img src="images/azl-fests.jpg"
                                                                                  alt='События и фестивали в пансионате «АзовЛенд»'></a>
                </div>
                <div class="textrow">
                    <a href="http://azovsky.ru/azovland/azovland-festivals/">События и фестивали в пансионате
                        «АзовЛенд»</a></div>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="mainDiv">
                    <a href="http://azovsky.ru/riopark/rio-festivals/"><img src="images/festivals/azovfest-m.jpg"
                                                                            alt='События и фестивали в парк-отеле «РИО»'></a>
                </div>
                <div class="textrow">
                    <a href="http://azovsky.ru/riopark/rio-festivals/">События и фестивали в парк-отеле «РИО»</a></div>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="mainDiv">
                    <a href="http://azovsky.ru/azovsky/azovskiy-festivals/"><img src="images/az-fests.jpg"
                                                                                 alt='События и фестивали в пансионате «Азовский»'></a>
                </div>
                <div class="textrow">
                    <a href="http://azovsky.ru/azovsky/azovskiy-festivals/">События и фестивали в пансионате
                        «Азовский»</a></div>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="mainDiv">
                    <a href="http://azovsky.ru/azovsky/cafe-i-bary/"><img src="images/azovsky/cafe/cafemain.jpg"
                                                                          alt='Кафе и бары пансионата "Азовский"'></a>
                </div>
                <div class="textrow">
                    <a href="http://azovsky.ru/azovsky/cafe-i-bary/">Кафе и бары пансионата «Азовский»</a></div>
            </div>

        </div>


    </div>
</div>


<div class="container">
    <div class="colorline"></div>
    <div class="offers">
        <div class="mainRow row" style="padding: 10px 15px 0px;">
            <div class="divh2">Специальные предложения курортной сети &laquo;Азовский&raquo;</div>
            <div class="col-md-3  col-sm-3 col-xs-12">
                <div class="offerPic">
                    <div class="skidka25"></div>
                    <img src="images/offers/offer1.jpg" alt='"Азовский" - Комфорт плюс'></div>
                <div class="offer">
                    <div class="row">
                        <div class="col-md-6 prlbl">"Азовский" - Комфорт плюс</div>
                        <div class="col-md-6 price">&#8381; 48 661</div>
                    </div>
                    <p><img src="images/persons.png" alt="люди">&nbsp;2взр + 1реб&nbsp;&nbsp;<img src="images/cal.png"
                                                                                                  alt="календарь">&nbsp;
                        03.06.17 - 12.06.17 </p>
                    <div class="buttons">
                        <form>
                            <a href="http://azovsky.ru/mail-us/" class="btn greybtn" style="text-transform:lowercase;">Бронировать</a>
                            <!--<input type="button" value="бронировать" class="btn greybtn">-->
                        </form>
                        <form>
                            <a href="http://azovsky.ru/mail-us/" class="btn orange" style="text-transform:lowercase;">Купить
                                онлайн</a>
                            <!--<input type="button" value="купить online" class="btn orange">-->
                        </form>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3  col-sm-3 col-xs-12">
                <div class="offerPic">
                    <div class="skidka20"></div>
                    <img src="images/offers/offer4.jpg" alt='"АзовЛенд" - Комфорт плюс'></div>
                <div class="offer">
                    <div class="row">
                        <div class="col-md-6 prlbl">"АзовЛенд" - Комфорт плюс панорамный вид на море</div>
                        <div class="col-md-6 price">&#8381; 40 241</div>
                    </div>
                    <p><img src="images/persons.png" alt="люди">&nbsp;2 ВЗР + 1 РЕБ&nbsp;&nbsp;<img src="images/cal.png"
                                                                                                    alt="календарь">&nbsp;
                        28.06.17. - 05.07.17</p>
                    <div class="buttons">
                        <form>
                            <a href="http://azovsky.ru/mail-us/" class="btn greybtn" style="text-transform:lowercase;">Бронировать</a>
                            <!--<input type="button" value="бронировать" class="btn greybtn">-->
                        </form>
                        <form>
                            <a href="http://azovsky.ru/mail-us/" class="btn orange" style="text-transform:lowercase;">Купить
                                онлайн</a>
                            <!--<input type="button" value="купить online" class="btn orange">-->
                        </form>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3  col-sm-3  col-xs-12">
                <div class="offerPic">
                    <div class="skidka25"></div>
                    <img src="images/offers/offer3.jpg" alt='"РИО" - Номера комфорт'></div>
                <div class="offer">
                    <div class="row">
                        <div class="col-md-6 prlbl">Пансионат "Азовский" - Семейные апартаменты 2-х комнатные</div>
                        <div class="col-md-6 price">&#8381; 52 568</div>
                    </div>
                    <p><img src="images/persons.png" alt="люди">&nbsp;4 ЧЕЛ&nbsp;&nbsp;<img src="images/cal.png"
                                                                                            alt="календарь">&nbsp;
                        05.06.17. - 12.06.17</p>
                    <div class="buttons">
                        <form>
                            <a href="http://azovsky.ru/mail-us/" class="btn greybtn" style="text-transform:lowercase;">Бронировать</a>
                            <!--<input type="button" value="бронировать" class="btn greybtn">-->
                        </form>
                        <form>
                            <a href="http://azovsky.ru/mail-us/" class="btn orange" style="text-transform:lowercase;">Купить
                                онлайн</a>
                            <!--<input type="button" value="купить online" class="btn orange">-->
                        </form>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="offerPic">
                    <div class="skidka20"></div>
                    <img src="images/offers/offer2.jpg" alt='"АзовЛенд" - Семейные номера'></div>
                <div class="offer">
                    <div class="row">
                        <div class="col-md-6 prlbl">"АзовЛенд" - Семейные номера панорамный вид на море</div>
                        <div class="col-md-6 price">&#8381; 45 270</div>
                    </div>
                    <p><img src="images/persons.png" alt="люди">&nbsp;4 ЧЕЛ&nbsp;&nbsp;<img src="images/cal.png"
                                                                                            alt="календарь">&nbsp;
                        27.05.17-04.06.17</p>
                    <div class="buttons">
                        <form>
                            <a href="http://azovsky.ru/mail-us/" class="btn greybtn" style="text-transform:lowercase;">Бронировать</a>
                            <!--<input type="button" value="бронировать" class="btn greybtn">-->
                        </form>
                        <form>
                            <a href="http://azovsky.ru/mail-us/" class="btn orange" style="text-transform:lowercase;">Купить
                                онлайн</a>
                            <!--<input type="button" value="купить online" class="btn orange">-->
                        </form>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>

            <form class="loadmore">
                <a href="http://azovsky.ru/specpredlojenia-dnia/" title="загрузить еще предложения"><input type="button"
                                                                                                           value="загрузить еще предложения"
                                                                                                           class="green"></a>
            </form>
        </div>
    </div>
</div>


<div class="container">
    <div class="colorline"></div>
    <div class="azov__line textline row">
        <div class="mainRow h2centerr" style="padding: 10px 80px 0px;">
            <h2>Отдых в Крыму - это незабываемое время</h2>
            <p>Необыкновенные пейзажи, ласковый ветерок, завораживающие волны, омолаживающая морская вода, теплый песок,
                оздоровительные солнечные ванны, удивительные растения, комфортабельные пансионаты. </p>
            <p>Стоит ли стремиться в отпуск в другую страну, если в России есть такие курорты! Вы, конечно, можете сами
                разработать себе маршрут, лично купить путевки, опираясь на советы и отзывы в Интернете. Кто-то
                бронирует путевки заранее, а кто-то предпочитает горящие путевки в Крым.</p>
            <p>Чтобы сэкономить время, деньги, а главное - сделать правильный выбор, обращайтесь в курортную сеть
                «Азовский». Наши пансионаты находятся в одном из самых живописных уголков полуострова. Это заповедник
                «Казантип», расположенный в 50 км от Феодосии. На большой территории множество необыкновенных растений,
                поэтому, несмотря на развитую инфраструктуру, пансионат кажется райским островом.</p>
            <p>Помимо бескрайнего чистого моря, ласкового солнца, сочных фруктов и прочих прелестей природы, мы
                предлагаем вам комфортные условия проживания, трехразовое питание и целый комплекс услуг. Для кого-то
                лучшим отдыхом будет релаксация на берегу, а кто-то предпочитает активное времяпровождение.
                Разнообразные экскурсии, конные походы, катание на теплоходе, общение с дружелюбными дельфинами и много
                других увлекательных мероприятий мы рекомендуем своим гостям.</p>
            <p>В 2016 году сезон продлится с 24 апреля по 16 октября. Этот период характеризуется благоприятной для
                отдыха солнечной погодой. Отдыхая в Крыму на Азовском море, вы не только замечательно проведете
                каникулы, но и заметно поправите здоровье. Мы с удовольствием примем всех тех, кто в теплое время года
                запланировал семейный или групповой приезд в Крым. Наша курортная сеть с радостью обеспечила бы
                путевками всех желающих, но количество мест в хороших пансионатах, к сожалению, ограничено. Поэтому мы
                рекомендуем вам бронировать путевки на море заранее. Осенью у нас проводится акция «Раннее
                бронирование», участники которой получают хорошую скидку на приобретение путевок.</p>
            <p>Стоимость проживания в наших пансионатах демократична и доступна для большинства россиян. По цене от 1320
                руб/сутки мы предоставим вам все необходимое для комфортного и беззаботного отдыха. Наше официальное
                представительство находится в г. Москва, где вы можете купить путевку на море без посредников. Помимо
                регулярных акций и специальных предложений, всегда есть скидки для пенсионеров, постоянных клиентов, на
                групповой заезд и на дополнительные места. </p>
            <p>Уровень обслуживания на российских курортах заметно возрос за последние годы. Многие люди, ранее
                предпочитавшие каникулы за границей, теперь выбирают пансионаты родной страны. В наше неспокойное время
                гораздо безопасней отдыхать среди соотечественников, чем подвергать близких людей лишним переживаниям,
                чтобы повидать заморские красоты.</p>
            <p>Мы предлагаем вам качественное обслуживание по доступной цене. Те, кто доверились нам однажды, очень
                часто приезжают и на следующий год.</p>
            <p>Планируя отдых в Крыму 2017, воспользуйтесь условиями акции на <a class="onblue"
                                                                                 href="http://azovsky.ru/rannee-bronirovanie-krym/"
                                                                                 title="Раннее бронирование">раннее
                бронирование</a>. Это позволит купить путевку со значительными скидками. По данной акции, сохраняются
                опции перебронирования или сдачи путевки, если планы на отпуск изменятся. Пансионаты нашей сети
                гарантируют отдых в 2017 году на объектах с развитой инфраструктурой, чистым морем, просторными пляжами,
                зажигательной анимацией, спортивными играми и многим другим!</p>
            <div class="divh2">Фотогалереи</div>
        </div>
    </div>
</div>

<div class="container">
    <div class="gallerymainbottom row">
        <a href="http://azovsky.ru/photo/" title="Фотогалереи">
            <div class="col-md-3 col-sm-3 col-xs-6"><img src="images/photo/photogalleryIMG_2505.jpg"
                                                         alt="Счастливый ребенок с арбузом"></div>
        </a>
        <a href="http://azovsky.ru/photo/" title="Фотогалереи">
            <div class="col-md-3 col-sm-3 col-xs-6"><img src="images/photo/photogalleryIMG_2500.jpg"
                                                         alt="Малыш на водной горке"></div>
        </a>
        <a href="http://azovsky.ru/photo/" title="Фотогалереи">
            <div class="col-md-3 col-sm-3 col-xs-6"><img src="images/photo/photogalleryIMG_2507.jpg"
                                                         alt="Ребенок в бассейне аквапарка"></div>
        </a>
        <a href="http://azovsky.ru/photo/" title="Фотогалереи">
            <div class="col-md-3 col-sm-3 col-xs-6"><img src="images/photo/photogalleryIMG_2504.jpg"
                                                         alt="Детские горки аквапарка"></div>
        </a>
    </div>
</div>

<div id="moscontmap" style="height:0;"></div>

<div class="telph">
    <div class="container">
        <div class="zvonite"><p class="zagolovok">Звоните прямо сейчас</p>
            <ul class="goroda">
                <li>Регионы России <span class="small-text">(звонок беспл.)</span><br><span class="call_phone_bottom"><a
                        href="tel:+78007751541"><span style="font-size: 17px">8 (800)</span> <span
                        style="font-size: 28px">775-15-41</span></a></span></li>
                <li>Москва / Все регионы<br><a href="tel:+74959846011"><span style="font-size: 13px">(495) </span><span
                        style="font-size: 19px">984-60-11</span></a></li>
                <li>Санкт-Петербург<br><a href="tel:+78126706206"><span style="font-size: 13px">(812)</span> <span
                        style="font-size: 19px">670-62-06</span></a></li>
                <li>Краснодар<br><a href="tel:+78612033619"><span style="font-size: 13px">(861)</span> <span
                        style="font-size: 19px">203-36-19</span></a></li>
            </ul>
        </div>
    </div>
    <div class="container"><p class="uph">Не нашли то, что искали? Оставьте заявку, и мы вам перезвоним!</p>
        <form name="telmetwo" action="http://azovsky.ru/callme.php" method="POST">
            <div class="obrph"><input type="text" name="myname" placeholder="ваше имя" required><input type="text"
                                                                                                       name="myphone"
                                                                                                       placeholder="ваш телефон"
                                                                                                       required><input
                    type="submit" name="submittel" value="Отправить"></div>
        </form>
    </div>
</div>
<link href="css/client.css" rel="stylesheet">
<div id="slideout"></div>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <p class="h4p modal-title" id="myModalLabel">Перезвоните мне!</p>
            </div>
            <form name="telme" action="http://azovsky.ru/callme.php" method="POST">
                <div class="modal-body">
                    <input type="text" name="myname" class="form-control" placeholder="Моё имя..." required><br>
                    <input type="text" name="myphone" class="form-control" placeholder="Мой телефон..." required>
                </div>
                <div class="modal-footer">
                    <input type="submit" name="submittel" value="Отправить" class="btn btn-primary">
                </div>
            </form>

        </div>
    </div>
</div>
<footer>
    <div class="container">
        <div class="col-md-4 col-sm-7 col-xs-12 nopadleft" style="padding-left:30px;"><p class="h4p">Навигация</p>
            <div class="col-md-6 col-sm-6 col-xs-12" style="padding-left:0px;">
                <ul>
                    <li><a href="http://azovsky.ru/rannee-bronirovanie-krym/" title="Отдых лето 2017">&#9658; Отдых лето
                        2017</a></li>
                    <li><a href="index.html" title="Главная">&#9658; Главная</a></li>
                    <li><a href="http://azovsky.ru/promo/" title="Акции и скидки">&#9658; Акции и скидки</a></li>
                    <li><a href="http://azovsky.ru/about/" title="О нас">&#9658; О нас</a></li>
                    <li><a href="http://azovsky.ru/specpredlojenia-dnia/" title="Спецпредложения">&#9658;
                        Спецпредложения</a></li>
                    <li><a href="http://azovsky.ru/ceny-na-otdyh-v-krymu/" title="Цены">&#9658; Цены</a></li>
                    <li><a href="http://azovsky.ru/photo/" title="Фотогалереи">&#9658; Фотогалереи</a></li>
                    <li><a href="http://azovsky.ru/otdyh-s-detmi-v-krymu/" title="Отдых с детьми">&#9658; Отдых с
                        детьми</a></li>
                    <li><a href="http://azovsky.ru/animation/" title="Анимация">&#9658; Анимация</a></li>
                </ul>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <ul>
                    <li><a href="http://azovsky.ru/sports/" title="Спорт">&#9658; Спорт</a></li>
                    <li><a href="http://azovsky.ru/spa/" title="SPA">&#9658; SPA</a></li>
                    <li><a href="http://azovsky.ru/excursions/" title="Экскурсии">&#9658; Экскурсии</a></li>
                    <li><a href="http://azovsky.ru/news/" title="Новости">&#9658; Новости</a></li>
                    <li><a href="http://azovsky.ru/festivals/" title="События">&#9658; События</a></li>
                    <li><a href="http://azovsky.ru/road/" title="Как добраться">&#9658; Как добраться</a></li>
                    <li><a href="http://azovsky.ru/payment/" title="Как купить">&#9658; Как купить</a></li>
                    <li><a href="http://azovsky.ru/contact/" title="Контакты">&#9658; Контакты</a></li>
                    <li><a href="http://azovsky.ru/blog/" title="Блог">&#9658; Блог</a></li>
                </ul>
            </div>
        </div>
        <div class="col-md-3 col-sm-5 col-xs-12 nopadleft"><a href="http://azovsky.ru/news/" title="Новости"><p
                class="h4p">Новости</p></a>
            <div class="onenews">
                <div class="row">
                    <div class="col-md-4 col-sm-4 col-xs-4"
                         style="background:url(images/news/9NeEi54NDf9e.jpg)no-repeat;background-size:cover;background-position:center;height:100px;"></div>
                    <div class="col-md-8 col-sm-8 col-xs-8"><p class="title"><a
                            href="http://azovsky.ru/news/shvedsky-stol-v-pansionate-azovland/"
                            title="Шведский стол в пансионате">Шведский стол в пансионате</a></p>
                        <p class="text">В пансионате "АзовЛенд", начиная с сезона... </p>
                        <p class="data">26.09.2016</p></div>
                </div>
            </div>
            <a href="http://azovsky.ru/blog/" title="Блог"><p class="h4p">Блог</p></a>
            <div class="onenews">
                <div class="row">
                    <div class="col-md-4 col-sm-4 col-xs-4"
                         style="background:url(images/news/ht27NZFhRh4D.jpg)no-repeat;background-size:cover;background-position:center;height:100px;"></div>
                    <div class="col-md-8 col-sm-8 col-xs-8"><p class="title"><a
                            href="http://azovsky.ru/blog/arbuzny-rai/"
                            title="«Арбузный рай»  в пансионатах «АзовЛенд» и «Азовский»">«Арбузный рай» в пансионатах
                        «АзовЛенд» и «Азовский»</a></p>
                        <p class="text">27 августа и 29 августа прошёл самый вкусный... </p>
                        <p class="data">27.08.2016</p></div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-7 col-xs-12 nopadleft" style="padding-left:0px;"><p class="h4p">Поиск</p>
            <form class="searchmini" method="GET" action="http://azovsky.ru/search2.php"><input type="text" name="str"
                                                                                                placeholder="Поиск..."
                                                                                                required><input
                    type="submit" name="submit" value="Поиск"></form>
            <div class="clear"></div>
            <br>
            <p class="h4p">Подписка</p>
            <p class="onlyinfo">Подпишитесь на новости, чтобы всегда быть в курсе наших акций и предложений</p>
            <form method="POST" action="http://azovsky.ru/submit.php"><input type="text" id="rscbottom"
                                                                             name="submitmail"></form>
            <p class="onlyinfo">Рассылка не чаще 2 раз в месяц</p>
            <p class="onlyinfo"><a href="http://azovsky.ru/obraztsi-zayavleniy/" title="Образцы заявлений">Образцы
                заявлений</a></p></div>
        <div class="col-md-3 col-sm-5 col-xs-12 nopadleft"><p class="h4p">О нас</p>
            <p class="onlyinfo">Отдых в пансионате "Азовский" идеален для восстановления здоровья и душевных сил, нового
                заряда бодрости, для солнечных впечатлений и радостных эмоций всей семьи.</p>
            <p class="phone footTitle"><img src="images/phone.png" alt="Пишите нам">&nbsp;<a href="tel:+78007751541">8-800-775-15-41</a>
            </p>
            <p class="footMail"><a title="Пишите нам" href="http://azovsky.ru/mail-us/">Пишите нам!</a></p>
            <div class="social">
                <ul>
                    <li><a href="https://vk.com/azovsky" rel="nofollow" target="_blank" title="Мы в Контакте"><img
                            src="images/vk.png" alt="Мы В Контакте"></a></li>
                    <li><a href="https://www.facebook.com/pansionat.azovsky" rel="nofollow" target="_blank"
                           title="Мы в Facebook"><img src="images/fb.png" alt="Мы в Facebook"></a></li>
                    <li><a href="http://ok.ru/group/53035953750263" target="_blank" rel="nofollow" title="Мы в OK"><img
                            src="images/ok.png" alt="Мы в OK"></a></li>
                    <li><a href="http://www.youtube.com/channel/UCdW01GfchaIveumpsKSSiEg" rel="nofollow" target="_blank"
                           title="Мы в Youtube"><img src="images/yt.png" alt="Мы в Youtube"></a></li>
                    <li><a href="https://instagram.com/azovskyresort/" target="_blank" rel="nofollow"
                           title="Мы в Instagram"><img src="images/gp.png" alt="Мы в Instagram"></a></li>
                </ul>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</footer>
<div class="subfooter">
    <div class="container">
        <div class="col-md-3 col-sm-4 col-xs-6"><a href="index.html#"><img src="images/logob.png" alt="logo"></a></div>
        <div class="col-md-9 col-sm-8 col-xs-6 text-right">2003-2016 Azovsky <img src="images/strtp.png" alt="Arrow"
                                                                                  class="top" id="top"></div>
    </div>
</div>
<!-- Yandex.Metrika counter -->
<noscript>
    <div><img src="http://mc.yandex.ru/watch/10885255" style="display:none; alt="" ></div>
</noscript>
<!-- /Yandex.Metrika counter -->
<!--Include all compiled plugins (below), or include individual files as needed-->


<script type="text/javascript" src="http://azovsky.ru/js/fancy/jquery.mousewheel-3.0.6.pack.js"></script>
<script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js"></script>
<script type="text/javascript" src="http://azovsky.ru/js/fancy/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
<script type="text/javascript" src="http://azovsky.ru/js/fancy/helpers/jquery.fancybox-media.js?v=1.0.6"></script>
<script type="text/javascript" src="http://azovsky.ru/js/fancy/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>


<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#top').click(function () {
            $("body,html").animate({"scrollTop": 0}, 1000);
        });
        $(".inpagegal a").fancybox();
    });
</script>
<!--<script language="javascript" type="text/javascript" src="http://onchat.4ww.ru/js/jquery-cookie.js"></script>-->
<script type="text/javascript" src="js/main.js"></script>
<script type="text/javascript" src="js/custom.js"></script>
<!--<script type="text/javascript">-->
<!--    var ZCallbackWidgetLinkId = 'dd43f9250c9f9a92194345eb027aaa55';-->
<!--    var ZCallbackWidgetDomain = 'ss.zadarma.com';-->
<!--    (function () {-->
<!--        var lt = document.createElement('script');-->
<!--        lt.type = 'text/javascript';-->
<!--        lt.charset = 'utf-8';-->
<!--        lt.async = true;-->
<!--        lt.src = 'https://' + ZCallbackWidgetDomain + '/callbackWidget/js/main.min.js?unq=' + Math.floor(Math.random(0, 1000) * 1000);-->
<!--        var sc = document.getElementsByTagName('script')[0];-->
<!--        if (sc) sc.parentNode.insertBefore(lt, sc);-->
<!--        else document.documentElement.firstChild.appendChild(lt);-->
<!--    })();-->
<!--</script>-->
<script type="text/javascript" charset="utf-8" async
        src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=hmYTL3w7YBEkPOocDY4_PYoqH45KAz0O&id=moscontmap&lang=ru_RU&sourceType=constructor&scroll=true"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $('a.example').click(function () {
            $('div.example').toggle('fast');
            return false;
        });
    });

</script>
</body>
</html>	