/**
 *  @class Aero utility object
 *  @author Mike Priest
 */
"use strict";

/**
 *  Global namespace
 */
var Aero = {

	/**
	 *  Constants
	 */
	constants : {
		PATH : AeroStep.baseUrl,
		USERNAME : AeroStep.getUsername()
	},

	/**
	 *  Models object
	 */
	model : {},

	/**
	 *  Views object
	 */
	view : {},

	/**
	 *  Get target app domain
	 */
	host : AeroStep.host,

	/**
	 *  XHR Requests
	 */
	send : function(url, data, callback, type) {

        //Default to GET
        if (!type) type = "GET";

		//Default host
		if(!data.host) data.host = this.host;
		if(!data.locale) data.locale = AeroStep.locale;

        //Limitation for POST PUT
		if (type == "POST" || type == "PUT") data = JSON.stringify(data);

        //Prefix URL
		url = this.constants.PATH + url;

		var options = {
			async: false,
			type: type,
			data: data,
			dataType: 'json',
			contentType: 'application/json',
			url: url,
			success: function(response) {
				if (callback) callback(response);
			},
			error: function(xhr, err) {
				var error = url +" says: "+ JSON.stringify(xhr) + " " + JSON.stringify(err);
				Aero.log(JSON.stringify(error));
			}
		};
		if (type == "GET") options.dataType = "jsonp";
		$q.ajax(options);
	},

	/**
	 * @function Log a message to the browser console using styling similar to bootstrap alerts
	 * @param {string} Either error, warn, info, success and defaults to plain text
	 */
	log : function(msg, state){
		if(!AeroStep.debug) return;

		if(state){
			switch(state){
				case "error":
					console.log('%c' + msg, 'background: #ea8181; color: #333');
					break;
				case "warn":
					console.log('%c' + msg, 'background: #eac481; color: #333');
					break;
				case "success":
					console.log('%c' + msg, 'background: #8eea81; color: #333');
					break;
				case "info":
					console.log('%c' + msg, 'background: #81d7ea; color: #333');
					break;
			}
		}else{
			console.log(msg);
		}
	}
};

/**
 * Utility object
 * @type {{getUrlParameter: Function}}
 */
Aero.utils = {

	/**
	 * Get URL parameter
	 * @param sParam
	 * @returns {*}
	 */
	getUrlParam : function getUrlParameter(sParam) {
		var sPageURL = decodeURIComponent(window.location.search.substring(1)),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : sParameterName[1];
			}
		}
	}
};


/**
 *  Modal window
 */
Aero.confirm = function(options){

	/**
	 *  Modal defaults
	 */
	var $modal;
	var defaults = {
		ok : AeroStep.lang.ok,
		cancel : AeroStep.lang.cancel,
		title : "",
		msg : "",
		confirm : false,
        onClose : function(){},
		onConfirm : function(){},
		onCancel : function(){},
		onValidate : function(){ return true; }
	};
	$q.extend(defaults, options);

	/**
	 *  Show the modal
	 *  @param object options
	 */
	function show(){
		Aero.tpl.get("modal.html", function(r){

			//Append template
			var tpl = _q.template(r);
			$q('body').append( tpl( { msg: defaults }));

			//Get modal
			$modal = $q('.aero-modal').hide();
			defaults.height = $modal.outerHeight();

			// Put modal in place
			$modal.css({
				top : ($q(window).height() / 2) - (defaults.height / 2),
				left : ($q(window).width() / 2) - ($modal.outerWidth() / 2)
			});

			$q('.aero-backdrop').fadeTo(200, 0.5, function() {
				$modal.show();
				$q('.aero-modal-body').find('input:eq(0)').focus();
			});

			setEvents(defaults.onConfirm);
		});
	}

	/**
	 *  Hide the modal
	 */
	function hide(){

		//Get modal
		$modal = $q('.aero-modal');

		//Animate
		$modal.animate({
			top : -(defaults.height)
		}, 300, function(){
			$q('.aero-backdrop, .aero-modal').fadeTo(200, 0).remove();
		});
	}

	/**
	 *  Try validate and close
	 */
	function doConfirm(){
		if(defaults.onValidate()){
			defaults.onConfirm();
			hide();
		}
	}
	/**
	 *  Set events
	 */
	function setEvents(){

		//Shortcuts
		$q('body').off("keyup.smw").on("keyup.smw", function(e){
			var k = e.keyCode || e.which;
			if(k == '27'){
				defaults.onCancel();
				hide();
			}
		});

        //Close
        $q('body').off("click.amwc").on("click.amwc", ".aero-modal-close", function(e){
            defaults.onClose();
            hide();
        });

		//Buttons
		$q('body')
			.off('click.confok')
			.on('click.confok', '.aero-btn-ok', function(){
				doConfirm();
			});

		$q('body')
			.off('click.confc')
			.on('click.confc', '.aero-btn-no', function(){
				defaults.onCancel();
				hide();
			});

		//Save on enter
		$q('.aero-modal').off("keypress.confk").on("keypress.confk", function(e){
			var k = e.keyCode || e.which;
			if(k == '13'){
				doConfirm();
				return false;
			}
		});
	}

	//Finally show the modal
	show();
};

$q.fn.scrollParent = function() {
    var overflowRegex = /(auto|scroll)/,
        position = this.css( "position" ),
        excludeStaticParent = position === "absolute",
        scrollParent = this.parents().filter( function() {
            var parent = $q( this );
            if ( excludeStaticParent && parent.css( "position" ) === "static" ) {
                return false;
            }
            return (overflowRegex).test( parent.css( "overflow" ) + parent.css( "overflow-y" ) + parent.css( "overflow-x" ) );
        }).eq( 0 );

    return position === "fixed" || !scrollParent.length ? $q( this[ 0 ].ownerDocument || document ) : scrollParent;
};

/**
 * Poistioning Helpers
 * @type {{isFixed: Function}}
 */
Aero.pos = {
    isFixed : function(element) {
		var $element = $q(element);
		var $checkElements = $element.add($element.parents());
		var isFixed = false;

        $checkElements.each(function(){
			if ($q(this).css("position") === "fixed") {
				isFixed = true;
				return false;
			}
		});
		return isFixed;
	},

	isScrollable : function($element){
        var $parent = $element.scrollParent();
		var tagName = $parent.prop('tagName');

        if(tagName && tagName != "HTML" && tagName != "BODY" && tagName != "DOCUMENT") return $parent;

        return false;
	}
}
/**
 * Template manager
 */
Aero.tpl = {
	templates: {},
	get: function(name, callback) {
        var self = this;
	    var path = "api/template";
	    var data = {
	    	name: name,
            guest: Aero.constants.USERNAME
	    };

	    if(self.templates[name]){
	    	if(callback) callback(self.templates[name]);
	    	return;
	    }

        Aero.send(path, data, function(r){

            var obj = JSON.parse(r);
            Aero.tpl.templates[name] = obj.tpl;
            if(callback) callback(self.templates[name]);
        });
	}
};

//$q Utilies
$q.fn.aeroSerialize = function()
{
    var o = {};
    var a = this.serializeArray();

    $q.each(a, function() {
    	 var name = this.name.replace("aero_", "");

    	if (o[name] !== undefined) {
            if (!o[name].push) {
                o[name] = [o[this.name]];
            }
            o[name].push(this.value || '');
        } else {
            o[name] = this.value || '';
        }
    });
    return o;
};

/**
 * Multi select dropdown
 * Must use unque ID on select
 */
(function( $ ){

    var callbacks = {
        onChange : function(){}
    };

    var methods = {
        /**
         * Initialize Multi Select
         * @param options
         */
        init : function(options) {

            var $select, $tpl, id, count;

            $select = $q(this);
            id = $q(this).attr('id');
            count = 0;

            //Override
            callbacks.onChange = options.onChange;

            //Hide it
            $select
                .hide()
                .data('multi-init', true);

            //Start template
            $tpl = $q('<div id="aeSelect'+id+'" class="aero-multi-select"><a class="aero-multi-drop"></a><ul style="display:none"></ul></div>');
            $select.after($tpl);

            //Add options
            $select.find("option").each(function() {
                if($(this).val() != "" && $(this).val() != Aero.tip._guide.id) {
                    var s = $(this).is(':selected') ? 'checked="true"' : '';

                    $tpl.find('ul').append('<li><label><input type="checkbox" '+ s +'value="' + this.value + '" />' + this.text + '</label></li>');
                    if(s != "") count++;
                }
            });

            var num = count > 0 ? count + " Selected": "Select Guides";
            $('#aeSelect'+id).find('.aero-multi-drop').html(num);

            methods.setEvents(id);
        },

        /**
         * Set events for this instance
         * @param id
         */
        setEvents : function(id){

            var mId = '#aeSelect' + id;

            //Click dropdown
            $('body').on('click', function(){
                $(mId).find('ul').hide();
            });

            //Click dropdown
            $(mId).find('a.aero-multi-drop')
                .on('click', function(e){
                    var $ul = $(this).parent().find('ul');
                    if(!$ul.is(':visible')) {
                        $(this).parent().find('ul').show();
                    }else{
                        $(this).parent().find('ul').hide();
                    }

                    e.stopPropagation();
                })
                .attr('unselectable', 'on')
                .css('user-select', 'none')
                .on('selectstart', false);

            $(mId).find('.aero-multi-select li, .aero-multi-select label').on('click', function(e){
                e.stopPropagation();
            });

            //On change select
            $(mId).find('input[type=checkbox]').on('change', function(e){
                $('#' + id + ' option[value=' + $(this).val() + ']').attr('selected', $(this).is(':checked'));

                var count = $(mId).find('input:checked').length;
                var num = count > 0 ? count + " Selected": "Select Guides";
                $(mId).find('.aero-multi-drop').html(num);

                callbacks.onChange(count);
                e.stopPropagation();
            });
        }
    };

    $.fn.multiSelect = function(methodOrOptions) {
        if ( methods[methodOrOptions] ) {
            return methods[ methodOrOptions ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof methodOrOptions === 'object' || ! methodOrOptions ) {
            // Default to "init"
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  methodOrOptions + ' does not exist on jQuery.tooltip' );
        }
    };

})( $q );

//Form Validate
$q.fn.isFormValid = function()
{
    var errors = [];

    //Clear
	$q('.aero-require-error').removeClass('aero-require-error');
	$q('.aero-error-box').remove();

	$q(this).find('.aero-required').each(function(){
		//Clear error
		$q(this).removeClass('aero-require-error');

		//Check error
		if($q(this).val() == ""){
			if(errors.length == 0) errors.push(AeroStep.lang.requirede);
			$q(this).addClass('aero-require-error');
		}
    });

	if(errors.length > 0){
		if($q('.aero-modal form:eq(0)').length > 0) $q('.aero-modal form:eq(0)').prepend('<div class="aero-error-box"><ul><li>'+errors.join('</li><li>')+'</li></ul></div>');
		return false;
	}

	return true;
};

/**
 * Check if element is visible in view port
 * @param partial
 * @returns {boolean}
 */
$q.fn.visible = function(partial, contain){

	var $w = contain ? contain : $q(window);
    var $t        = this.length > 1 ? this.eq(0) : this,
        t         = $t.get(0),
        vpWidth   = $w.width(),
        vpHeight  = $w.height();

    if (typeof t.getBoundingClientRect === 'function'){

        // Use this native browser method, if available.
        var rec = t.getBoundingClientRect(),
            tViz = rec.top    >= 0 && rec.top    <  vpHeight,
            bViz = rec.bottom >  0 && rec.bottom <= vpHeight,
            lViz = rec.left   >= 0 && rec.left   <  vpWidth,
            rViz = rec.right  >  0 && rec.right  <= vpWidth,
            vVisible   = partial ? tViz || bViz : tViz && bViz,
            hVisible   = partial ? lViz || rViz : lViz && rViz;

        return vVisible && hVisible;
    }
};

/**
 * jquery.unevent.js 0.2
 * https://github.com/yckart/jquery.unevent.js
 *
 * Copyright (c) 2013 Yannick Albert (http://yckart.com)
 * Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php).
 * 2013/07/26
 **/
;(function ($q) {
    var on = $q.fn.on, timer;
    $q.fn.on = function () {
        var args = Array.apply(null, arguments);
        var last = args[args.length - 1];

        if (isNaN(last) || (last === 1 && args.pop())) return on.apply(this, args);

        var delay = args.pop();
        var fn = args.pop();

        args.push(function () {
            var self = this, params = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(self, params);
            }, delay);
        });

        return on.apply(this, args);
    };
}(this.$q));


//Polyfill
if (!Object.keys) {
    Object.keys = function(obj) {
        var keys = [];

        for (var i in obj) {
            if (obj.hasOwnProperty(i)) {
                keys.push(i);
            }
        }

        return keys;
    };
}

//Fire Custom
if (typeof aerofire !== 'undefined') aerofire.init();

//Remove old JSON Prototyping
if(typeof Prototype !== 'undefined' && parseFloat(Prototype.Version.substr(0,3)) < 1.7 && typeof Array.prototype.toJSON !== 'undefined') {
    if(window.Prototype) {
        delete Object.prototype.toJSON;
        delete Array.prototype.toJSON;
        delete Hash.prototype.toJSON;
        delete String.prototype.toJSON;
    }
}
