/*global $*/
//JQuery
SideNavStatus = false;


GLOBALS = {}

BuildSideNav = function(cb, mode) {
	if (!SideNavStatus) {
		BuildSideNavInternal(cb, mode);
	}
}


BuildSideNavInternal = function(cb, mode = "expand") {
	$('.all-nav').show(); //CUSTOM
	$('#main-nav').hide();
	$('.side-nav-div').css('display', 'none');

	for (var x = navCount; x >= 0; x--) {
		$('.all-nav').removeClass('hc-nav-' + x);
		$('nav.hc-nav-' + x).remove();
	}
	
	

	setTimeout(function(cb) {

		var $main_nav = $('#main-nav');
		var $toggle = $('.toggle');


		var defaultData = {
			maxWidth: false,
			customToggle: $toggle,
			navTitle: '',
			levelTitles: true,
			pushContent: '#container',
			insertClose: 2,
			closeLevels: false,
			levelOpen: mode, //overlap, expand or false,
			levelSpacing: 0
		};

		// add new items to original nav
		$main_nav.find('li.add').children('a').on('click', function() {
			var $this = $(this);
			var $li = $this.parent();
			var items = eval('(' + $this.attr('data-add') + ')');

			$li.before('<li class="new"><a>' + items[0] + '</a></li>');

			items.shift();

			if (!items.length) {
				$li.remove();
			} else {
				$this.attr('data-add', JSON.stringify(items));
			}

			Nav.update(true);
		});

		// call our plugin
		Nav = $main_nav.hcOffcanvasNav(defaultData);

		// demo settings update

		const update = (settings) => {
			if (Nav.isOpen()) {
				Nav.on('close.once', function() {
					Nav.update(settings);
					Nav.open();
				});

				Nav.close();
			} else {
				Nav.update(settings);
			}
		};

		$('.actions').find('a').on('click', function(e) {
			e.preventDefault();

			var $this = $(this).addClass('active');
			var $siblings = $this.parent().siblings().children('a').removeClass('active');
			var settings = eval('(' + $this.data('demo') + ')');

			update(settings);
		});

		$('.actions').find('input').on('change', function() {
			var $this = $(this);
			var settings = eval('(' + $this.data('demo') + ')');

			if ($this.is(':checked')) {
				update(settings);
			} else {
				var removeData = {};
				$.each(settings, function(index, value) {
					removeData[index] = false;
				});

				update(removeData);
			}
		});

		$('.menu-loading').hide();
		$('.side-nav-div').css('display', 'inherit');
		$('#main-nav').hide();

		SideNavStatus = true;

		if (cb) cb();

	}, 1000, cb);

}


$(function() {
	// $('.sidenav').sidenav({
	// 	closeOnClick: true
	// });

	// $(document).ready(function(){
	//   	$('.collapsible').collapsible();
	// });	

	// $('select').formSelect();

	


	function htmlbodyHeightUpdate() {
		var height3 = $(window).height()
		var height1 = $('.nav').height() + 50
		height2 = $('.main').height()
		if (height2 > height3) {
			$('html').height(Math.max(height1, height3, height2) + 10);
			$('body').height(Math.max(height1, height3, height2) + 10);
		} else {
			$('html').height(Math.max(height1, height3, height2));
			$('body').height(Math.max(height1, height3, height2));
		}

	}


	$(document).ready(function() {

		htmlbodyHeightUpdate()
		$(window).resize(function() {
			htmlbodyHeightUpdate()
		});
		$(window).scroll(function() {
			height2 = $('.main').height()
			htmlbodyHeightUpdate()
		});


		for (i = 100; i <= 2000; i += 100) {
			setTimeout(function() {
				$('.all-nav').hide();
			}, i);
		}
		// setTimeout(function() {
		// 	$('.all-nav').hide();
		// }, 1000);
		// setTimeout(function() {
		// 	$('.all-nav').hide();
		// }, 2000);

	});
});
