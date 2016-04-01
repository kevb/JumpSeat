"use strict";

/**
 * View for Common Admin Screens (Guide or Step)
 * @author Mike Priest
 * @type {{guideid: string, type: string, render: Function, renderMenu: Function, renderMenuTip: Function, close: Function, edit: Function, renderTools: Function, setEvents: Function}}
 */
Aero.view.admin = {

	guideid : "",
	type : "",

	/**
	 *  Render steps in the menu list
	 */
	render : function(type, guide, tab){

		$q('.container-close').remove();
		this.type = type;
		if(Aero.tip._guide) Aero.tip.hide();
		$q('.aero-active').removeClass('aero-active');

		Aero.tpl.get("sidebar-"+this.type+"-admin.html", function(r){
			$q('.aero-admin-wrapper').remove();

			var tpl = _q.template(r);

			$q('body').append( tpl( { model: guide }));

			$q('#aero-tab').css('left', '-542px');
			$q('.aero-sidebar').prepend('<div class="aero-blanket" style="display:none"></div>');
			$q('.aero-blanket').fadeTo(300 , 0.9, function(){ });

			if(type == "step") $q('.aero-nav-item:not(:first)').append('<a class="aero-del-nav">X</a>');

            //Setup Editor
			$q('#aeroEditor').trumbowyg({
				removeformatPasted: true,
				fullscreenable: false,
				resetCss: true,
				autogrow: true,
				height:100
			});
        });

		setTimeout("$q('.aero-required:eq(0)').focus();", 300);

        if(tab) $q(tab).trigger('click');

	},

	/**
	 *  Render view for sub menu
	 */
	renderMenu : function($li){
		var i = $q('.aero-strip-menu li').index($li);
		$q('.aero-admin-body .open').removeClass('open');
		$q('.aero-strip-menu li').removeClass('aero-menu-active');

		$li.addClass('aero-menu-active');
		$q('#aeroSection' + i).addClass('open');

		//Guide screens
		if(i == 2 && this.type == "guide") Aero.view.guide.admin.renderRoles();
		if(i == 1 && this.type == "guide") Aero.view.guide.admin.renderPathways();
		if(i == 4 && this.type == "step") {
            //Check for input and validation
            var tag = $q( $q("input[name='aero_loc']").val() ).prop("tagName");

            if (tag != "INPUT") $q('input[name="aero_mask"]').attr('disabled', true).addClass('aero-disabled');
        }
		if(i == 5 && this.type == "step" && !$q('#aeroBranch').data('multi-init')) {
            $q('#aeroBranch').multiSelect({ onChange : function(count){
                (count > 0) ? $q('.aero-branch-step').show() : $q('.aero-branch-step').hide();
            }});
        }
	},

	/**
	 *  Render the hover tip
	 */
	renderMenuTip : function($a, title){
		var $tip = $q('<div class="aero-menu-tip" style="top:-200px"><span></span>' + title + '</div>');
		var o = $a.offset().top + ($tip.outerHeight() / 2) + 16;
		$q('.aero-app').append($tip);

		$tip.css( { 'top':  o - $q(window).scrollTop(), 'left' : 55, opacity: 0 });
		$tip.animate({
		    opacity: 1,
		    left: "+=8"
		  }, 100);
	},

	/**
	 *  Close edit view
	 */
	close : function(){
		var show = ($q('.aero-editing-step').hasClass('aero-add-step') ? false : true);
		$q('.aero-admin-wrapper, .aero-edit-active').remove();
		$q('#aero-tab').css('left', '-44px');
		$q('.aero-blanket').fadeTo( "medium" , 0, function(){ $q(this).remove(); });
		$q('.aero-editing').removeClass('aero-editing');

		//Show step again?
		if(Aero.tip._guide && show){
			var step = Aero.step.get(Aero.tip._current);
			Aero.tip.beforeShow(Aero.tip._current, step);
		}
	},

	/**
	 *  Edit a model
	 */
	edit : function($this, loc){

        var tab =  false;
        var self = this;
		var $li = $this.parents('li:eq(0)');
		var type = Aero.tip._guide ? "step" : "guide";
		self.guideid = Aero.tip._guide ? Aero.tip._guide.id : $this.parents('li:eq(0)').data('guideid');

		$li.addClass('aero-editing');
		$li.prepend('<span class="aero-edit-active"></span>');

		Aero.guide.get(self.guideid, function(guide){
			if(type == "step"){
				guide = guide['step'][Aero.index];
				guide.id = self.guideid;
				guide.index = Aero.index;
				guide.isAdd = false;

				if(loc){
					guide.loc = loc;
                    tab = ".aicon-loc";
				}
			}

			Aero.view.admin.render(type, guide, tab);
		});
	},

	/**
	 *  Render admin tools
	 */
	renderTools : function($el){

		var $tools = $el.find('.aero-item-tools:eq(0)');

		if($tools.length > 0){
			$tools.show();
			return;
		}

		//Go get it
		var type = $el.data('guideid') ? "guide" : "step";
		var guideid = $el.data('guideid') || Aero.tip._guide.id;

		Aero.tpl.get("sidebar-"+type+"-tools.html", function(r){
			Aero.model.guide.byId(guideid, function(guide){
				var tpl = _q.template(r);
				$el.prepend( tpl( { creator: (guide.creator == Aero.constants.USERNAME) }));
			});
		});
	},

	/**
	 *  Create events for admin UX
	 */
	setEvents : function(type){
		var self = this;
		if(type) self.type = type;

		$q('body')
		//Cancel
		.off("click.aeroCancel").on("click.aeroCancel", ".aero-cancel-edit", function(){
			self.close();
			return false;
		})
		//Menu enter
		.off("mouseenter.aerome").on("mouseenter.aerome", ".aero-strip-menu a", function() {
			$q('.aero-menu-tip').remove();
			Aero.view.admin.renderMenuTip($q(this), $q(this).data('title'));
        })
        //Menu leave
        .off("mouseleave.aeroml").on("mouseleave.aeroml", ".aero-strip-menu a", function() {
        	$q('.aero-menu-tip').remove();
        })
		//Tools hover
		.off("click.aeroTools").on("mouseenter.aeroTools", ".aero-"+self.type+"s ul li", function(){
			Aero.view.admin.renderTools($q(this));
			return false;
		})
		//Tools leave
		.off("click.aeroTools").on("mouseleave.aeroTools", ".aero-"+self.type+"s ul li", function(){
			$q('.aero-item-tools').hide();
		})
		//branch
		.off("change.aerob").on("change.aerob", "select[name='aero_branch']", function(){
			$q('select[name="aero_return"]').parents('div:eq(0)').hide();
			if($q(this).val() != "") $q('select[name="aero_return"]').parents('div:eq(0)').show();
		});
	}
};
