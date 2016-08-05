/**
 *  Common utility library for admin
 *  @class Lib
 *  @package Lib
 */
var Utils = {};

/**
 *  Word utilities
 */
Utils.words = {

		/**
		 *  Cut a paragraph to character count
		 *  cut word so no partials
		 */
		cut : function(text, n){
            var sm = text.substr(0, n);
            if (/^\S/.test(text.substr(n))) return sm.replace(/\s+\S*$/, "");
            return sm;
		}
};


/**
 *  Menu tooltip
 */
Utils.menuTip = {

	/**
	 *  Animate the menu tooltip
	 */
	show : function($a){
		var title = $a.data('title');
		var $tip = $q('<div class="tip" style="top:-200px"><span></span>' + title + '</div>');
		$q('body').append($tip);

		var top = ($a.parents('li:eq(0)').offset().top - 10) + ($tip.outerHeight() / 2);

		$tip.css( { 'top': top, 'left' : 60, opacity: 0 });
		$tip.animate({
		    opacity: 1,
		    left: "+=8"
		  }, 100);
	},

	/**
	 *  Remove the tooltip once mouse leaves
	 */
	hide : function(){
		$q('.tip').remove();
	},

	/**
	 *  Set Events function
	 */
	setEvents : function(){
		var self = this;

		$q('#sidebar a')
			.on('mouseenter', function(){
				self.show($q(this));
			})
			.on('mouseleave', function(){
				self.hide();
			});
	}
};
//Start menu tip
Utils.menuTip.setEvents();


/**
 *  Waiting, Saving, Error
 */
Utils.message = function(message, type, time){

	if(!type) type = "default";
	if(!time) time = 2000;

	var $msg = $q('<div class="aero-msg-'+type+'" style="display:none">'+message+'</div>');
	$q('body').append($msg);

	$msg
		.css({
			'left' : ($q(window).width() / 2) - ($msg.outerWidth() / 2)
	 	})
	 	.fadeIn(750, function(){
	 		setTimeout(function(){
	 			$msg.fadeOut(500);
	 		}, time);
	 	});
};


/**
 *  Multi function utiles
 */
Utils.menu = function(menu, settings){

	var m = this;
		m.el = $q("#"+$q(menu).parents('div:eq(0)').attr('id'));
		m.ns = menu.replace('#', '');

		//Defaults
		m.events = {
			onEdit : function(r){},
			onDelete : function(r){},
			onExport : function(r){},
			onImport : function(r){},
			onClone : function(r){},
			onFind: function(r){}
		};

	//Extend defaults
	$q.extend(m.events, settings);

	/**
	 *  Get flagged ids
	 */
	function getFlagged(){
		var ids = [];

		m.el.find(".select:checked").each(function(){
			ids.push($q(this).data('id'));
		});

		return ids;
	}

	/**
	 *  Disable button
	 */
	function disable(){
		m.el.find('.multi').click().addClass('disabled');
	}

	/**
	 * Set events
	 */
	function setEvents(){

        $q('#menu .multi').data('orig', $q('#menu .multi').text());

		//Check All
		$q('body').off('click.sa' + m.ns).on('click.sa' + m.ns, '.select-all', function(){

            if($q('.select-all').is(':checked')){
				$q('.select').prop('checked', true);
				$q('#menu .multi').text($q('#menu .multi').data('orig').replace('With', $q('.select:checked').length));
            }else{
                $q('.select').prop('checked', false);
                $q('#menu .multi').text($q('#menu .multi').data('orig'));
            }

			m.el.find('.multi').addClass('disabled');
			var ids = getFlagged();
			if(ids.length > 0) m.el.find('.multi').removeClass('disabled');
		});

		//Checked
		$q('body').off('click.s' + m.ns).on('click.s' + m.ns, '.select', function(){
			m.el.find('.multi').addClass('disabled');
			var ids = getFlagged();
			if(ids.length > 0) {
                m.el.find('.multi').removeClass('disabled');
                $q('#menu .multi').text($q('#menu .multi').data('orig').replace('With', $q('.select:checked').length));
            }else{
                $q('#menu .multi').text($q('#menu .multi').data('orig'));
            }
		});

		//Delete
		m.el.off('click.d' + m.ns).on('click.d' + m.ns, '.multi-delete', function(){
			m.events.onDelete(getFlagged());
			disable();
		});

		//Edit
		m.el.off('click.e' + m.ns).on('click.e' + m.ns, '.multi-edit', function(){
			m.events.onEdit(getFlagged());
			disable();
		});

		//Export
		m.el.off('click.ex' + m.ns).on('click.ex' + m.ns, '.multi-export', function(){
			m.events.onExport(getFlagged());
		});

		//Clone
		m.el.off('click.cl' + m.ns).on('click.cl' + m.ns, '.multi-clone', function(){
			m.events.onClone(getFlagged());
		});

		//Find
		m.el.off('click.e' + m.ns).on('click.e' + m.ns, '.multi-find', function(){
			m.events.onFind(getFlagged());
			disable();
		});

		//Anything that gets to the document will hide the dropdown
		$q(document).click(function(){
			if(parseInt(m.el.find('.f-dropdown').css('left')) > 0)
				m.el.find('.multi').click();
			if(parseInt($q('#userMenu .f-dropdown').css('left')) > 0)
				$q('#userMenu .multi').click();
		});
	}
	setEvents();
};


/**
 *  Card Flipping
 */
Utils.card = {

	/**
	 *  Flip a card
	 */
	flip : function($el, toFront, callback){

		var $card = $el.parents('.card');
		var $front = $card.find('.front');
		var $back = $card.find('.back');

		if($card.length == 0){
			callback();
			return;
		}

		// Get IE version
		var ie = (function(){
		    var undef,
		        v = 3,
		        div = document.createElement('div'),
		        all = div.getElementsByTagName('i');

		    while (
		        div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i>< ![endif]-->',
		        all[0]
		    );

		    return v > 4 ? v : undef;
		}());

		// Animated card flip for all browsers and IEs 10 and up
        if(!ie || ie > 9){
			TweenMax.set([ $back, $front ], {
				backfaceVisibility : "hidden"
			});

			var tl = new TimelineMax({paused:true});

			if(!toFront){
				TweenMax.set($back, {rotationY:-180});
				$card.find('.back').show();
				tl
					.to($front, 0.5, {rotationY:180})
					.to($back, 0.5, {rotationY:0},0);
				tl.play();
			}else{
				tl
					.to($front, 0.5, {rotationY:0})
					.to($back, 0.5, {rotationY:-180},0)
				tl.play();
			}

		// Toggle card for IE 9 and below
        }else{
        	if(!toFront){
				$back.css({
					'position': 'static'
				}).show();
				$front.hide();
			}else{
				$front.show();
				$back.hide();
			}
        }

        if(!toFront){
        	setTimeout(function(){ $back.find('input:eq(0)').focus(); }, 500);
        }else{
        	$q('body').focus();
        }
	},

	/**
	 *  Cancel the new card
	 */
	remove :function($el, callback){
		var total = $el.parents('ul').find('li').length;

		if(total > 1){
			$el.parents('li:eq(0)').fadeOut(200, function(){
				$q(this).remove();
			});
		}else{
			if(App) App.id = null;
			$q('.card-wrapper:eq(0)').data('id', false);
			$q('input').val("");
			$q('textarea').val("");
		}
	},

	setHeight : function(self){
		var $card = $q(self).parents('.back:eq(0)');
		$card.height($q(self).parents('form').outerHeight());
	},

	/**
	 *  Set mouse events
	 */
	setEvents :function(lang){
		$q('.front').off("mouseenter.cardme").on("mouseenter.cardme", function(){
			var w = $q(this).outerWidth(), l = (w/2) - 7;
			var $tip = $q('<div class="card-tip" style="width:' + w + 'px"><span style="left:'+l+'px"></span><div>'+lang+'</div></div>');
			$q(this).append($tip);
		});
		$q('.front').off("mouseleave.cardml").on("mouseleave.cardml", function(){
			$q(this).find('.card-tip').remove();
		});
	}
};


/**
 *  Datatable object
 */
Utils.datatable = function(url, columns, id){

    var table;
    var api = url.split("/")[0];
    var host = (url.indexOf("?") == -1) ? "?host=" : "&host=";

    //Optional id
    if(id) api = id;

    var t = "/api/"+ url + host + Aero.host +'&id='+ id;

    $q('#'+api+' table tbody').html("");
    table = $('#'+api+' table').DataTable({
		"pageLength": 25,
        "ajax" : '/api/'+ url + host + Aero.host +'&id='+ id,
        "order": [[ 1, "asc" ]],
        "columnDefs": [{ "targets": 0, "orderable": false }, { "targets": (columns.length - 1), "orderable": false }],
        "columns": columns,
        "oLanguage": {
            "sSearch": AeroStep.lang.dtfilter,
            "sLengthMenu": AeroStep.lang.dtentries,
            "sInfoFiltered": AeroStep.lang.dtfiltered,
            "sInfo": AeroStep.lang.dtinfo,
            "sEmptyTable": AeroStep.lang.dtempty,
            "sInfoEmpty": AeroStep.lang.dtinfoempty,
            "sZeroRecords": AeroStep.lang.dtzero,
            "oPaginate": {
                "sNext": AeroStep.lang.dtnext,
                "sPrevious": AeroStep.lang.dtprevious
            }
        }
    });

    //Draw the table
    table.draw();


    //Switch labels
    $q('#DataTables_Table_0_filter input').attr('placeholder', AeroStep.lang.dtfilterplace + '...');
    $q('.next').html("&rarr;");
    $q('.previous').html("&larr;");

    return table;
};
