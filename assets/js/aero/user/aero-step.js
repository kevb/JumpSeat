"use strict";

/** Import plugins */
/*
 Masked Input plugin for jQuery
 Copyright (c) 2007-2011 Josh Bush (digitalbush.com)
 Licensed under the MIT license (http://digitalbush.com/projects/masked-input-plugin/#license)
 Version: 1.3
 */
(function(a){var b=(a.browser.msie?"paste":"input")+".mask",c=window.orientation!=undefined;a.mask={definitions:{9:"[0-9]",a:"[A-Za-z]","*":"[A-Za-z0-9]"},dataName:"rawMaskFn"},a.fn.extend({caret:function(a,b){if(this.length!=0){if(typeof a=="number"){b=typeof b=="number"?b:a;return this.each(function(){if(this.setSelectionRange)this.setSelectionRange(a,b);else if(this.createTextRange){var c=this.createTextRange();c.collapse(!0),c.moveEnd("character",b),c.moveStart("character",a),c.select()}})}if(this[0].setSelectionRange)a=this[0].selectionStart,b=this[0].selectionEnd;else if(document.selection&&document.selection.createRange){var c=document.selection.createRange();a=0-c.duplicate().moveStart("character",-1e5),b=a+c.text.length}return{begin:a,end:b}}},unmask:function(){return this.trigger("unmask")},mask:function(d,e){if(!d&&this.length>0){var f=a(this[0]);return f.data(a.mask.dataName)()}e=a.extend({placeholder:"_",completed:null},e);var g=a.mask.definitions,h=[],i=d.length,j=null,k=d.length;a.each(d.split(""),function(a,b){b=="?"?(k--,i=a):g[b]?(h.push(new RegExp(g[b])),j==null&&(j=h.length-1)):h.push(null)});return this.trigger("unmask").each(function(){function v(a){var b=f.val(),c=-1;for(var d=0,g=0;d<k;d++)if(h[d]){l[d]=e.placeholder;while(g++<b.length){var m=b.charAt(g-1);if(h[d].test(m)){l[d]=m,c=d;break}}if(g>b.length)break}else l[d]==b.charAt(g)&&d!=i&&(g++,c=d);if(!a&&c+1<i)f.val(""),t(0,k);else if(a||c+1>=i)u(),a||f.val(f.val().substring(0,c+1));return i?d:j}function u(){return f.val(l.join("")).val()}function t(a,b){for(var c=a;c<b&&c<k;c++)h[c]&&(l[c]=e.placeholder)}function s(a){var b=a.which,c=f.caret();if(a.ctrlKey||a.altKey||a.metaKey||b<32)return!0;if(b){c.end-c.begin!=0&&(t(c.begin,c.end),p(c.begin,c.end-1));var d=n(c.begin-1);if(d<k){var g=String.fromCharCode(b);if(h[d].test(g)){q(d),l[d]=g,u();var i=n(d);f.caret(i),e.completed&&i>=k&&e.completed.call(f)}}return!1}}function r(a){var b=a.which;if(b==8||b==46||c&&b==127){var d=f.caret(),e=d.begin,g=d.end;g-e==0&&(e=b!=46?o(e):g=n(e-1),g=b==46?n(g):g),t(e,g),p(e,g-1);return!1}if(b==27){f.val(m),f.caret(0,v());return!1}}function q(a){for(var b=a,c=e.placeholder;b<k;b++)if(h[b]){var d=n(b),f=l[b];l[b]=c;if(d<k&&h[d].test(f))c=f;else break}}function p(a,b){if(!(a<0)){for(var c=a,d=n(b);c<k;c++)if(h[c]){if(d<k&&h[c].test(l[d]))l[c]=l[d],l[d]=e.placeholder;else break;d=n(d)}u(),f.caret(Math.max(j,a))}}function o(a){while(--a>=0&&!h[a]);return a}function n(a){while(++a<=k&&!h[a]);return a}var f=a(this),l=a.map(d.split(""),function(a,b){if(a!="?")return g[a]?e.placeholder:a}),m=f.val();f.data(a.mask.dataName,function(){return a.map(l,function(a,b){return h[b]&&a!=e.placeholder?a:null}).join("")}),f.attr("readonly")||f.one("unmask",function(){f.unbind(".mask").removeData(a.mask.dataName)}).bind("focus.mask",function(){m=f.val();var b=v();u();var c=function(){b==d.length?f.caret(0,b):f.caret(b)};(a.browser.msie?c:function(){setTimeout(c,0)})()}).bind("blur.mask",function(){v(),f.val()!=m&&f.change()}).bind("keydown.mask",r).bind("keypress.mask",s).bind(b,function(){setTimeout(function(){f.caret(v(!0))},0)}),v()})}})})($q);

/**
 * Step Model
 * @author Mike Priest
 * @type {{url: string, defaults: Function, update: Function, validate: Function}}
 */
Aero.model.step = {

	/**
	 *  Services URI
	 */
	url : "api/steps",

	/**
	 *  @function defaults object for steps
	 *  @param {integer} i step index
	 *  @param {function} callback function
	 */
	defaults : function(i){
		var next = i + 1;
		if(Aero.tip._guide.step) next = (i === Aero.tip._guide.step.length - 1) ? -1 : i + 1;

		return {
			loc: "",
			tries: 3,
			position: "bottom",
			id: "astep-" + i,
			next: next,
			prev: i - 1
		}
	},

	update : function(guide, ignoreShow){

		//Update ls
		aeroStorage.getItem('aero:session:index', function(index){

			var ls = JSON.parse(aeroStorage.getItem('aero:guides'));

			//Index Int
			index = parseInt(index);

			//Merge new object
			ls[index] = guide;

			//Update local storage
            aeroStorage.setItem('aero:session:end', 0);
			aeroStorage.setItem('aero:guides', JSON.stringify(ls), function(){});
            aeroStorage.setItem('aero:session', JSON.stringify(ls[index]), function(){
                //Set session
                Aero.tip.setGuide(guide.id, function(){

                    //Make sure we have enough steps
                    if(Aero.tip._current == Aero.tip._guide.step.length) Aero.tip._current--;
                    if(!Aero.tip._current) Aero.tip._current = 0;
                    if(!ignoreShow) Aero.tip.jumpTo(Aero.tip._current);
                });
			}, true);
		}, true);
	},

	validate : function(){

		//Validate
		var valid = $q('form').isFormValid();

		//Check content
		if($q('#aeroEditor').trumbowyg('html').replace(/<br>/g, '') == ""){
			valid = false;
			$q('.trumbowyg-editor').addClass('aero-require-error');
		}

		//Add messaging
		if(!valid){
			$q('#aeroSection0 p:first').after('<div class="aero-error-box">'+AeroStep.lang.requirede+'</div>');

			//Switch Tab?
			var $tabC = $q('.aero-require-error:eq(0)').parents('.aero-tab-content:eq(0)');
			if($tabC){
				$tabC.parent().find('a[href="#' + $tabC.attr('id') + '"]').click();
			}
		}

		return valid;
	}
};

/**
 * Step View
 * @author Mike Priest
 * @type {{render: Function, renderShare: Function, setState: Function, setEvents: Function}}
 */
Aero.view.step = {

	/**
	 *  Render steps in the menu list
	 */
	render : function(guide){

		var self = this;

		Aero.tpl.get("sidebar-steps.html", function(r){
			aeroStorage.getItem('aero:sidebar:open', function(s){
				aeroStorage.getItem('aero:session:tab', function(tab){
					//Remove duplicates
					$q('#aeroStepbar').remove();

					var tpl = _q.template(r);
					$q('body').append( tpl( { sidebar: s, g : guide }));
					$q('#aeroGuidebar').remove();

                    if(tab > $q('#aeroStepbar').outerHeight()) tab = $q('#aeroStepbar').outerHeight() - 100;
                    if(tab < 0) tab = 0;

					$q('#aero-tab').css("top", tab + "px");
					self.setEvents();
					Aero.view.sidebar.setScrollable();
				}, true);
			}, true);
		});

		Aero.view.sidebar.setEvents();
	},

	/**
	 *  Render sharing link tooltip
	 */
	renderShare : function(url){
		$q('body').append('<div class="aero-share-tip"><span></span><input type="text" value="'+url+'" /></div>');
		$q('.aero-share-tip input').select();
	},

	/**
	 *  Set sidebar UI
	 */
	setState : function(i, state){
		if(!i) i = Aero.tip._current;

		if(state){
			$q('.aero-steps li:eq('+i+')').attr('class', 'clearfix aero-' + state);
		}else{
			var $li = $q('.aero-steps li:eq('+i+')');

			$li.addClass('aero-active');
			$li.removeClass('aero-forward aero-missing');
			aeroStorage.removeItem("aero:session:forward", function(){}, true);
		}

        //Scroll to active step
        var $parentDiv = $q('div.aero-steps');
        var $innerListItem = $q('li.aero-active');
        var $adminButton = $q('div.aero-add-step');
        var adjust = 0;

        if($innerListItem.length > 0) {
            //Admin Button
            if ($adminButton.length > 0) adjust += $adminButton.outerHeight();

            $parentDiv.animate({
                scrollTop: $parentDiv.scrollTop() + $innerListItem.position().top
                - $parentDiv.height() / 2 + $innerListItem.height() / 2 - adjust - $q('.aero-guide-title').outerHeight()
            });
        }
	},

	/**
	 *  Set event handlers
	 */
	setEvents : function(){

		var self = this;

		if(AeroStep.admin){
            $q('body').off("click.sa").on("click.sa", ".aero-steps li > a", function(){
                Aero.tip.jumpTo($q('.aero-steps li').index($q(this).parent()));
            });
        }

		//Play button
		$q('body').off('click.aplay').on('click.aplay', 'a.aero-play-icon', function(){
			Aero.view.step.setState(parseInt(aeroStorage.getItem('aero:session:pause')), 'non');
			aeroStorage.removeItem('aero:session:pause');

			Aero.tip.jumpTo(Aero.tip._current);
			$q(this).fadeOut(1000, function(){ $q(this).remove(); });
		});

		$q('body').off("click.sas").on("click.sas", "a.aero-stop", function(){
			Aero.tip.stop();
		});

		$q('body').off("click.ashare").on("click.ashare", "a.aero-share", function(){
			self.renderShare(Aero.host + $q(this).data('link'));
			return false;
		});

		$q(document).click(function(evt){
			if($q(evt.target).hasClass('aero-share-tip') || $q(evt.target).parent().hasClass('aero-share-tip')) return;
			$q('.aero-share-tip').remove();
		});

        //Tab drag
        $q('#aero-tab').draggable({
            axis: "y",
            distance: 10,
			containment: 'body',
            stop: function( event, ui ) {
                aeroStorage.setItem('aero:session:tab', ui.offset.top, function(){}, true);
            }
        });

		//If admin
		if(AeroStep.admin) setTimeout('if(Aero.view.step.admin) Aero.view.step.admin.renderSortable();', 100);
	}
};

/**
 * Step Object
 * @author Mike Priest
 * @type {{get: Function, add: Function, update: Function, moveIndex: Function, destroy: Function}}
 */
Aero.step = {

	/**
	 * @function Get step data
	 * @param {integer} i Step number
     * @returns {object} step object
	 */
	get : function(i){
        try {
            //Extend defaults with user options
            var model = new Aero.model.step.defaults(i);
            return $q.extend({}, model, Aero.tip._guide.step[i]);
        }catch(err){
            AeroStep.session.destroy();
            location.reload(true);
        }
	},

	/**
	 * @function Add a new step
	 * @param {object} step object
     * @param {function} callback function
	 */
	add : function(step, callback, ignoreShow){

		//Actions
		step.id = Aero.tip._guide.id;
		step.insertAt = Aero.tip._current ? Aero.tip._current : 0;

		Aero.send(Aero.model.step.url, step, function(r){

            if(Aero.tip._guide.step.length != 0) {
                Aero.tip._current++;
            }

            //Update ls
			Aero.model.step.update(r, ignoreShow);

			if(callback) callback(r);
		}, "POST");
	},


	/**
	 * @function Add a new step
	 * @param {object} step object
     * @param {function} callback function
	 */
	update : function(index, step, callback){

		//Actions
		step.id = Aero.tip._guide.id;
		step.index = index;

		Aero.send(Aero.model.step.url, step, function(r){

			//Update ls
			Aero.model.step.update(r);

			if(callback) callback(r);
		}, "PUT");
	},

	/**
	 * @function Move steps to new indexes
	 * @param {integer} from Step number
     * @param {integer} to Step number
	 */
	moveIndex : function(from, to){

		Aero.send(Aero.model.step.url + "/move", { id: Aero.tip._guide.id, from: from, to:to }, function(r){
			Aero.model.step.update(r);
		}, "PUT");
	},

	/**
	 * @function Delete a step
	 * @param {integer} i Step number
	 * @param {function} callback function
	 */
	destroy : function(i, callback){

		Aero.send(Aero.model.step.url, { id: Aero.tip._guide.id, index:i}, function(r){

			//Update ls
			Aero.model.step.update(r);

			if(callback) callback(r);
		}, "DELETE");
	}
};
