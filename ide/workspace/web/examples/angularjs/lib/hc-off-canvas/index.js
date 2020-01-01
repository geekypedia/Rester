/*
 * HC Off-canvas Nav
 * ===================
 * Version: 3.4.1
 * Author: Some Web Media
 * Author URL: http://somewebmedia.com
 * Plugin URL: https://github.com/somewebmedia/hc-offcanvas-nav
 * Description: jQuery plugin for creating off-canvas multi-level navigations
 * License: MIT
 */
 
 navCount = 0;
'use strict';



function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($, window) {
  var document = window.document;
  var $html = $(document.getElementsByTagName('html')[0]);
  var $document = $(document);

  var hasScrollBar = function hasScrollBar() {
    return document.documentElement.scrollHeight > document.documentElement.clientHeight;
  };

  var isIos = function () {
    return (/iPad|iPhone|iPod/.test(navigator.userAgent) || !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform)) && !window.MSStream;
  }();

  var isTouchDevice = function () {
    return 'ontouchstart' in window || navigator.maxTouchPoints || window.DocumentTouch && document instanceof DocumentTouch;
  }();

  var isNumeric = function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
  };

  var toMs = function toMs(s) {
    return parseFloat(s) * (/\ds$/.test(s) ? 1000 : 1);
  };

  var ID = function ID() {
    return Math.random().toString(36).substr(2) + '-' + Math.random().toString(36).substr(2);
  };

  var stopPropagation = function stopPropagation(e) {
    return e.stopPropagation();
  };

  var preventClick = function preventClick(cb) {
    return function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (typeof cb === 'function') cb();
    };
  };

  var browserPrefix = function browserPrefix(prop) {
    var prefixes = ['Webkit', 'Moz', 'Ms', 'O'];
    var thisBody = document.body || document.documentElement;
    var thisStyle = thisBody.style;
    var Prop = prop.charAt(0).toUpperCase() + prop.slice(1);

    if (typeof thisStyle[prop] !== 'undefined') {
      return prop;
    }

    for (var i = 0; i < prefixes.length; i++) {
      if (typeof thisStyle[prefixes[i] + Prop] !== 'undefined') {
        return prefixes[i] + Prop;
      }
    }

    return false;
  };

  var getElementCssTag = function getElementCssTag(el) {
    return typeof el === 'string' ? el : el.attr('id') ? '#' + el.attr('id') : el.attr('class') ? el.prop('tagName').toLowerCase() + '.' + el.attr('class').replace(/\s+/g, '.') : getElementCssTag(el.parent()) + ' ' + el.prop('tagName').toLowerCase();
  };

  var printStyle = function printStyle(id) {
    var $style = $("<style id=\"".concat(id, "\">")).appendTo($('head'));
    var rules = {};
    var media = {};

    var parseRules = function parseRules(text) {
      if (text.substr(-1) !== ';') {
        text += text.substr(-1) !== ';' ? ';' : '';
      }

      return text;
    };

    return {
      reset: function reset() {
        rules = {};
        media = {};
      },
      add: function add(selector, declarations, query) {
        selector = selector.trim();
        declarations = declarations.trim();

        if (query) {
          query = query.trim();
          media[query] = media[query] || {};
          media[query][selector] = parseRules(declarations);
        } else {
          rules[selector] = parseRules(declarations);
        }
      },
      remove: function remove(selector, query) {
        selector = selector.trim();

        if (query) {
          query = query.trim();

          if (typeof media[query][selector] !== 'undefined') {
            delete media[query][selector];
          }
        } else {
          if (typeof rules[selector] !== 'undefined') {
            delete rules[selector];
          }
        }
      },
      insert: function insert() {
        var cssText = '';

        for (var breakpoint in media) {
          cssText += "@media screen and (".concat(breakpoint, ") {\n");

          for (var key in media[breakpoint]) {
            cssText += "".concat(key, " { ").concat(media[breakpoint][key], " }\n");
          }

          cssText += '}\n';
        }

        for (var _key in rules) {
          cssText += "".concat(_key, " { ").concat(rules[_key], " }\n");
        }

        $style.html(cssText);
      }
    };
  };

  var insertAt = function insertAt($insert, n, $parent) {
    var $children = $parent.children();
    var count = $children.length;
    var i = n > -1 ? Math.max(0, Math.min(n - 1, count)) : Math.max(0, Math.min(count + n + 1, count));

    if (i === 0) {
      $parent.prepend($insert);
    } else {
      $children.eq(i - 1).after($insert);
    }
  };

  var getAxis = function getAxis(position) {
    return ['left', 'right'].indexOf(position) !== -1 ? 'x' : 'y';
  };

  var setTransform = function () {
    var transform = browserPrefix('transform');
    return function ($el, val, position) {
      if (transform) {
        if (val === 0) {
          $el.css(transform, '');
        } else {
          if (getAxis(position) === 'x') {
            var x = position === 'left' ? val : -val;
            $el.css(transform, x ? "translate3d(".concat(x, "px,0,0)") : '');
          } else {
            var y = position === 'top' ? val : -val;
            $el.css(transform, y ? "translate3d(0,".concat(y, "px,0)") : '');
          }
        }
      } else {
        $el.css(position, val);
      }
    };
  }();

  var deprecated = function () {
    var pluginName = 'HC Off-canvas Nav';
    return function (what, instead, type) {
      console.warn('%c' + pluginName + ':' + '%c ' + type + "%c '" + what + "'" + '%c is now deprecated and will be removed. Use' + "%c '" + instead + "'" + '%c instead.', 'color: #fa253b', 'color: default', 'color: #5595c6', 'color: default', 'color: #5595c6', 'color: default');
    };
  }();

  navCount = 0;
  $.fn.extend({
    hcOffcanvasNav: function hcOffcanvasNav() {
      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      if (!this.length) return this;
      var self = this; // get body of the current document

      var $body = $(document.body);
      var defaults = {
        maxWidth: 1024,
        pushContent: false,
        position: 'left',
        // left, right, top
        levelOpen: 'overlap',
        // overlap, expand, none/false
        levelSpacing: 40,
        levelTitles: false,
        navTitle: null,
        navClass: '',
        disableBody: true,
        closeOnClick: true,
        customToggle: null,
        insertClose: true,
        insertBack: true,
        labelClose: 'Close',
        labelBack: 'Back',
        autoCloseLevels: false 
      };

      if (options.side) {
        deprecated('side', 'position', 'option');
        options.position = options.side;
      }

      var Settings = $.extend({}, defaults, options);
      var UpdatedSettings = [];
      var navOpenClass = 'nav-open';

      var checkForUpdate = function checkForUpdate(options) {
        if (!UpdatedSettings.length) {
          return false;
        }

        var hasUpdated = false;

        if (typeof options === 'string') {
          options = [options];
        }

        var l = options.length;

        for (var i = 0; i < l; i++) {
          if (UpdatedSettings.indexOf(options[i]) !== -1) {
            hasUpdated = true;
          }
        }

        return hasUpdated;
      };

      var Plugin = function Plugin() {
        var $this = $(this);

        if (!$this.find('ul').addBack('ul').length) {
          console.error('%c! HC Offcanvas Nav:' + "%c Menu must contain <ul> element.", 'color: #fa253b', 'color: default');
          return;
        } // count our nav


        navCount++;
        var navUniqId = "hc-nav-".concat(navCount);
        var Styles = printStyle("hc-offcanvas-".concat(navCount, "-style"));
        var $toggle; // add classes to original menu so we know it's connected to our copy

        $this.addClass("hc-nav ".concat(navUniqId)); // this is our nav

        var $nav = $('<nav>').on('click', stopPropagation); // prevent menu close on self click

        var $nav_container = $('<div class="nav-container">').appendTo($nav);
        var $push_content = null;
        var Model = {};
        var _open = false;
        var _top = 0;
        var _containerWidth = 0;
        var _containerHeight = 0;

        var _transitionProperty;

        var _transitionDuration;

        var _transitionFunction;

        var _closeLevelsTimeout = null;
        var _indexes = {}; // object with level indexes

        var _openLevels = []; // array with current open levels
        // toggle

        if (!Settings.customToggle) {
          $toggle = $("<a class=\"hc-nav-trigger ".concat(navUniqId, "\"><span></span></a>")).on('click', toggleNav);
          $this.after($toggle);
        } else {
          $toggle = $(Settings.customToggle).addClass("hc-nav-trigger ".concat(navUniqId)).on('click', toggleNav);
        }

        var calcNav = function calcNav() {
          // remove transition from the nav container so we can update the nav without flickering
          $nav_container.css('transition', 'none');
          _containerWidth = $nav_container.outerWidth();
          _containerHeight = $nav_container.outerHeight(); // fix 100% transform glitching

          Styles.add(".hc-offcanvas-nav.".concat(navUniqId, ".nav-position-left .nav-container"), "transform: translate3d(-".concat(_containerWidth, "px, 0, 0)"));
          Styles.add(".hc-offcanvas-nav.".concat(navUniqId, ".nav-position-right .nav-container"), "transform: translate3d(".concat(_containerWidth, "px, 0, 0)"));
          Styles.add(".hc-offcanvas-nav.".concat(navUniqId, ".nav-position-top .nav-container"), "transform: translate3d(0, -".concat(_containerHeight, "px, 0)"));
          Styles.add(".hc-offcanvas-nav.".concat(navUniqId, ".nav-position-bottom .nav-container"), "transform: translate3d(0, ".concat(_containerHeight, "px, 0)"));
          Styles.insert(); // clear our 'none' inline transition

          $nav_container.css('transition', '');
          pageContentTransition();
        };

        var pageContentTransition = function pageContentTransition() {
          _transitionProperty = $nav_container.css('transition-property').split(',')[0];
          _transitionDuration = toMs($nav_container.css('transition-duration').split(',')[0]);
          _transitionFunction = $nav_container.css('transition-timing-function').split(',')[0];

          if (Settings.pushContent && $push_content && _transitionProperty) {
            Styles.add(getElementCssTag(Settings.pushContent), "transition: ".concat(_transitionProperty, " ").concat(_transitionDuration, "ms ").concat(_transitionFunction));
          }

          Styles.insert();
        }; // init function


        var initNav = function initNav(reinit) {
          var toggleDisplay = $toggle.css('display');
          var mediaquery = Settings.maxWidth ? "max-width: ".concat(Settings.maxWidth - 1, "px") : false; // clear media queries from previous run

          if (checkForUpdate('maxWidth')) {
            Styles.reset();
          } // create main styles


          Styles.add(".hc-offcanvas-nav.".concat(navUniqId), 'display: block', mediaquery);
          Styles.add(".hc-nav-trigger.".concat(navUniqId), "display: ".concat(toggleDisplay && toggleDisplay !== 'none' ? toggleDisplay : 'block'), mediaquery);
          Styles.add(".hc-nav.".concat(navUniqId), 'display: none', mediaquery);
          Styles.add(".hc-offcanvas-nav.".concat(navUniqId, ".nav-levels-overlap.nav-position-left li.level-open > .nav-wrapper"), "transform: translate3d(-".concat(Settings.levelSpacing, "px,0,0)"), mediaquery);
          Styles.add(".hc-offcanvas-nav.".concat(navUniqId, ".nav-levels-overlap.nav-position-right li.level-open > .nav-wrapper"), "transform: translate3d(".concat(Settings.levelSpacing, "px,0,0)"), mediaquery);
          Styles.add(".hc-offcanvas-nav.".concat(navUniqId, ".nav-levels-overlap.nav-position-top li.level-open > .nav-wrapper"), "transform: translate3d(0,-".concat(Settings.levelSpacing, "px,0)"), mediaquery);
          Styles.add(".hc-offcanvas-nav.".concat(navUniqId, ".nav-levels-overlap.nav-position-bottom li.level-open > .nav-wrapper"), "transform: translate3d(0,".concat(Settings.levelSpacing, "px,0)"), mediaquery);
          Styles.insert(); // get page content

          if (!reinit || reinit && checkForUpdate('pushContent')) {
            if (typeof Settings.pushContent === 'string') {
              $push_content = $(Settings.pushContent);

              if (!$push_content.length) {
                $push_content = null;
              }
            } else if (Settings.pushContent instanceof jQuery) {
              $push_content = Settings.pushContent;
            } else {
              $push_content = null;
            }
          } // remove transition from the nav container so we can update the nav without flickering


          $nav_container.css('transition', 'none');
          var wasOpen = $nav.hasClass(navOpenClass);
          var navClasses = ['hc-offcanvas-nav', Settings.navClass || '', navUniqId, Settings.navClass || '', 'nav-levels-' + Settings.levelOpen || 'none', 'nav-position-' + Settings.position, Settings.disableBody ? 'disable-body' : '', isIos ? 'is-ios' : '', isTouchDevice ? 'touch-device' : '', wasOpen ? navOpenClass : ''].join(' ');
          $nav.off('click').attr('class', '').addClass(navClasses); // close menu on body click (nav::after)

          if (Settings.disableBody) {
            $nav.on('click', closeNav);
          }

          if (reinit) {
            calcNav();
          } else {
            // timed out so we can get computed data
            setTimeout(calcNav, 1);
          }
        }; // create nav model function


        var createModel = function createModel() {
          // get first level menus
          var $first_level = function $first_level() {
            var $ul = $this.find('ul').addBack('ul'); // original nav menus

            return $ul.first().add($ul.first().siblings('ul'));
          }; // call


          Model = getModel($first_level());

          function getModel($menu) {
            var level = [];
            $menu.each(function () {
              var $ul = $(this);
              var nav = {
                classes: $ul.attr('class'),
                items: []
              };
              $ul.children('li').each(function () {
                var $li = $(this);
                var $content = $li.children().filter(function () {
                  var $this = $(this);
                  return $this.is(':not(ul)') && !$this.find('ul').length;
                }).add($li.contents().filter(function () {
                  return this.nodeType === 3 && this.nodeValue.trim();
                }));
                var $nested_navs = $li.find('ul');
                var $subnav = $nested_navs.first().add($nested_navs.first().siblings('ul')); // save unique identifier for remembering open menus

                if ($subnav.length && !$li.data('hc-uniqid')) {
                  $li.data('hc-uniqid', ID());
                } // add elements to this level


                nav.items.push({
                  uniqid: $li.data('hc-uniqid'),
                  classes: $li.attr('class'),
                  $content: $content,
                  subnav: $subnav.length ? getModel($subnav) : []
                });
              });
              level.push(nav);
            });
            return level;
          }
        }; // create nav DOM function


        var createNavDom = function createNavDom(reinit) {
          if (reinit) {
            // empty the container
            $nav_container.empty(); // reset indexes

            _indexes = {};
          } // call


          createDom(Model, $nav_container, 0, Settings.navTitle);

          function createDom(menu, $container, level, title, backIndex) {
            var $wrapper = $("<div class=\"nav-wrapper nav-wrapper-".concat(level, "\">")).appendTo($container).on('click', stopPropagation);
            var $content = $('<div class="nav-content">').appendTo($wrapper); // titles

            if (title) {
              $content.prepend("<h2>".concat(title, "</h2>"));
            }

            $.each(menu, function (i_nav, nav) {
              var $menu = $("<ul>").addClass(nav.classes).appendTo($content);
              $.each(nav.items, function (i_item, item) {
                var $item_content = item.$content;
                var $item_link = $item_content.find('a').addBack('a');
                var $a = $item_link.length ? $item_link.clone(true, true).addClass('nav-item') : $("<span class=\"nav-item\">").append($item_content.clone(true, true)).on('click', stopPropagation); // on click trigger original link

                if ($item_link.length) {
                  $a.on('click', function (e) {
                    e.stopPropagation();
                    $item_link[0].click();
                  });
                }

                if ($a.attr('href') === '#') {
                  // prevent page jumping
                  $a.on('click', function (e) {
                    e.preventDefault();
                  });
                } // close nav on item click


                if (Settings.closeOnClick) {
                  if (Settings.levelOpen === false || Settings.levelOpen === 'none') {
                    // every item should close the nav
                    $a.filter('a').filter('[data-nav-close!="false"]').on('click', closeNav);
                  } else {
                    // only items without submenus,
                    // or with submenus but with valid hrefs
                    $a.filter('a').filter('[data-nav-close!="false"]').filter(function () {
                      var $this = $(this);
                      return !item.subnav.length || $this.attr('href') && $this.attr('href').charAt(0) !== '#';
                    }).on('click', closeNav);
                  }
                }

                var $item = $("<li>").addClass(item.classes).append($a); // insert item

                $menu.append($item); // indent levels in expanded levels

                if (Settings.levelSpacing && (Settings.levelOpen === 'expand' || Settings.levelOpen === false || Settings.levelOpen === 'none')) {
                  var indent = Settings.levelSpacing * level;

                  if (indent) {
                    $menu.css('text-indent', "".concat(indent, "px"));
                  }
                } // do subnav


                if (item.subnav.length) {
                  var nextLevel = level + 1;
                  var uniqid = item.uniqid;
                  var nav_title = ''; // create new level

                  if (!_indexes[nextLevel]) {
                    _indexes[nextLevel] = 0;
                  } // li parent class


                  $item.addClass('nav-parent');

                  if (Settings.levelOpen !== false && Settings.levelOpen !== 'none') {
                    var index = _indexes[nextLevel];
                    var $next_span = $('<span class="nav-next">').appendTo($a);
                    var $next_label = $("<label for=\"".concat(navUniqId, "-").concat(nextLevel, "-").concat(index, "\">")).on('click', stopPropagation);
                    var $checkbox = $("<input type=\"checkbox\" id=\"".concat(navUniqId, "-").concat(nextLevel, "-").concat(index, "\">")).attr('data-level', nextLevel).attr('data-index', index).val(uniqid).on('click', stopPropagation).on('change', checkboxChange); // nav is updated, we should keep this level open

                    if (_openLevels.indexOf(uniqid) !== -1) {
                      $wrapper.addClass('sub-level-open').on('click', function () {
                        return closeLevel(nextLevel, index);
                      }); // close on self click

                      $item.addClass('level-open');
                      $checkbox.prop('checked', true);
                    }

                    $item.prepend($checkbox); // subnav title

                    nav_title = Settings.levelTitles === true ? $item_content.text().trim() : '';

                    if (!$a.attr('href') || $a.attr('href').charAt(0) === '#') {
                      $a.prepend($next_label.on('click', function () {
                        // trigger parent click in case it has custom click events
                        $(this).parent().trigger('click');
                      }));
                    } else {
                      $next_span.append($next_label);
                    }
                  }

                  _indexes[nextLevel]++;
                  createDom(item.subnav, $item, nextLevel, nav_title, _indexes[nextLevel] - 1);
                }
              });
            }); // insert back links

            if (level && typeof backIndex !== 'undefined') {
              if (Settings.insertBack !== false && Settings.levelOpen === 'overlap') {
                var $children_menus = $content.children('ul');
                var $back = $("<li class=\"nav-back\"><a href=\"#\">".concat(Settings.labelBack || '', "<span></span></a></li>"));
                $back.children('a').on('click', preventClick(function () {
                  return closeLevel(level, backIndex);
                }));

                if (Settings.insertBack === true) {
                  $children_menus.first().prepend($back);
                } else if (isNumeric(Settings.insertBack)) {
                  insertAt($back, Settings.insertBack, $children_menus);
                }
              }
            } // insert close link


            if (level === 0 && Settings.insertClose !== false) {
              var $nav_ul = $content.children('ul');
              var $close = $("<li class=\"nav-close\"><a href=\"#\">".concat(Settings.labelClose || '', "<span></span></a></li>"));
              $close.children('a').on('click', preventClick(closeNav));

              if (Settings.insertClose === true) {
                $nav_ul.first().prepend($close);
              } else if (isNumeric(Settings.insertClose)) {
                insertAt($close, Settings.insertClose, $nav_ul.first().add($nav_ul.first().siblings('ul')));
              }
            }
          }
        }; // init nav


        initNav(); // init our Model

        createModel(); // create view from model

        createNavDom(); // insert nav to DOM

        $body.append($nav); // Private methods

        function checkboxChange() {
          var $checkbox = $(this);
          var l = Number($checkbox.attr('data-level'));
          var i = Number($checkbox.attr('data-index'));

          if ($checkbox.prop('checked')) {
            openLevel(l, i);
          } else {
            closeLevel(l, i);
          }
        }

        function openNav() {
          _open = true;
          $nav.css('visibility', 'visible').addClass(navOpenClass);
          $toggle.addClass('toggle-open');

          if (Settings.levelOpen === 'expand' && _closeLevelsTimeout) {
            clearTimeout(_closeLevelsTimeout);
          }

          if (Settings.disableBody) {
            _top = $html.scrollTop() || $body.scrollTop(); // remember scroll position

            if (hasScrollBar()) {
              $html.addClass('hc-nav-yscroll');
            }

            $body.addClass('hc-nav-open');

            if (_top) {
              $body.css('top', -_top);
            }
          }

          if ($push_content) {
            var transformVal = getAxis(Settings.position) === 'x' ? _containerWidth : _containerHeight;
            setTransform($push_content, transformVal, Settings.position);
          } // trigger open event


          setTimeout(function () {
            self.trigger('open', $.extend({}, Settings));
          }, _transitionDuration + 1);
        }

        function closeNav() {
          _open = false;

          if ($push_content) {
            setTransform($push_content, 0);
          }

          $nav.removeClass(navOpenClass);
          $nav_container.removeAttr('style');
          $toggle.removeClass('toggle-open');

            if(Settings.autoCloseLevels){
                  if (Settings.levelOpen === 'expand' && ['top', 'bottom'].indexOf(Settings.position) !== -1) {
                    // close all levels before closing the nav because the nav height changed
                    closeLevel(0);
                  } else if (Settings.levelOpen !== false && Settings.levelOpen !== 'none') {
                    // close all levels when nav closes
                    _closeLevelsTimeout = setTimeout(function () {
                      // keep in timeout so we can prevent it if nav opens again before it's closed
                      closeLevel(0);
                    }, Settings.levelOpen === 'expand' ? _transitionDuration : 0);
                  }
            }

          if (Settings.disableBody) {
            $body.removeClass('hc-nav-open');
            $html.removeClass('hc-nav-yscroll');

            if (_top) {
              $body.css('top', '').scrollTop(_top);
              $html.scrollTop(_top);
              _top = 0; // reset top
            }
          }

          setTimeout(function () {
            $nav.css('visibility', ''); // trigger close event

            self.trigger('close.$', $.extend({}, Settings)); // only trigger close event once and detach it

            self.trigger('close.once', $.extend({}, Settings)).off('close.once');
          }, _transitionDuration + 1);
        }

        function toggleNav(e) {
          e.preventDefault();
          e.stopPropagation();
          if (_open) closeNav();else openNav();
        }

        function openLevel(l, i) {
          var $checkbox = $("#".concat(navUniqId, "-").concat(l, "-").concat(i));
          var uniqid = $checkbox.val();
          var $li = $checkbox.parent('li');
          var $wrap = $li.closest('.nav-wrapper');
          $wrap.addClass('sub-level-open');
          $li.addClass('level-open'); // remember what is open

          if (_openLevels.indexOf(uniqid) === -1) {
            _openLevels.push(uniqid);
          }

          if (Settings.levelOpen === 'overlap') {
            $wrap.on('click', function () {
              return closeLevel(l, i);
            }); // close on self click

            setTransform($nav_container, l * Settings.levelSpacing, Settings.position);

            if ($push_content) {
              var transformVal = getAxis(Settings.position) === 'x' ? _containerWidth : _containerHeight;
              setTransform($push_content, transformVal + l * Settings.levelSpacing, Settings.position);
            }
          }
        }

        var _closeLevel = function _closeLevel(l, i, transform) {
          var $checkbox = $("#".concat(navUniqId, "-").concat(l, "-").concat(i));
          var uniqid = $checkbox.val();
          var $li = $checkbox.parent('li');
          var $wrap = $li.closest('.nav-wrapper');
          $checkbox.prop('checked', false);
          $wrap.removeClass('sub-level-open');
          $li.removeClass('level-open'); // this is not open anymore

          if (_openLevels.indexOf(uniqid) !== -1) {
            _openLevels.splice(_openLevels.indexOf(uniqid), 1);
          }

          if (transform && Settings.levelOpen === 'overlap') {
            $wrap.off('click').on('click', stopPropagation); //level closed, remove wrapper click

            setTransform($nav_container, (l - 1) * Settings.levelSpacing, Settings.position);

            if ($push_content) {
              var transformVal = getAxis(Settings.position) === 'x' ? _containerWidth : _containerHeight;
              setTransform($push_content, transformVal + (l - 1) * Settings.levelSpacing, Settings.position);
            }
          }
        };

        function closeLevel(l, i) {
          for (var level = l; level <= Object.keys(_indexes).length; level++) {
            if (level == l && typeof i !== 'undefined') {
              _closeLevel(l, i, true);
            } else {
              // also close all sub sub levels
              for (var index = 0; index < _indexes[level]; index++) {
                _closeLevel(level, index, level == l);
              }
            }
          }
        } // Public methods


        self.settings = function (option) {
          return option ? Settings[option] : Object.assign({}, Settings);
        };

        self.isOpen = function () {
          return $nav.hasClass(navOpenClass);
        };

        self.open = openNav;
        self.close = closeNav;

        self.update = function (options, updateDom) {
          // clear updated array
          UpdatedSettings = [];

          if (_typeof(options) === 'object') {
            // only get what's been actually updated
            for (var prop in options) {
              if (Settings[prop] !== options[prop]) {
                UpdatedSettings.push(prop);
              }
            } // merge to our settings


            Settings = $.extend({}, Settings, options); // reinit DOM

            initNav(true);
            createNavDom(true);
          }

          if (options === true || updateDom) {
            // reinit model and DOM
            initNav(true);
            createModel();
            createNavDom(true);
          }
        };
      };

      return this.each(Plugin);
    }
  });
})(jQuery, typeof window !== 'undefined' ? window : this);
