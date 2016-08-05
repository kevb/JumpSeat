"use strict";

/**
 * Admin View for Steps
 * @author Mike Priest
 * @type {{addNav: Function, destroyNav: Function, save: Function, initPicker: Function, renderSortable: Function, setEvents: Function}}
 */
Aero.view.step.admin = {

    /**
     *  Delete nav item
     */
    addNav : function(){
        $q('.aero-nav-item:first').clone().append('<a class="aero-del-nav">X</a>').insertAfter('.aero-nav-item:last');
    },

    /**
     *  Delete nav item
     */
    destroyNav : function($this){
        if($q('.aero-nav-item'))
            $this.parents('.aero-nav-item').remove();
    },

    /**
     *  Save step data
     */
    save : function(){
        var index = parseInt($q('.aero-step-index').val());
        var data = $q('#editStepFrm').aeroSerialize();
        data.id = Aero.view.admin.guideid;
        data.body = $q('#aeroEditor').trumbowyg('html');
        data.nav = {};
        data.branch = $q('#aeroBranch').val() != "" ? $q('#aeroBranch').val() : null;

        if(Aero.host != location.protocol+'//'+window.location.host) data.cds = location.protocol+'//'+window.location.host;

        //Check for custom size and move into size prop
        if(data.size_custom != "" && !isNaN(data.size_custom + "")){
            data.size = data.size_custom + '';
        }
        //Not an actual prop
        delete data.size_custom;

        //Collect Quiz Data
        var quizData = Aero.view.step.admin.quiz.collectAnswers();
        data.answers = (quizData.length > 0) ? quizData : null;

        //Grab nav
        $q('.aero_nav_when').each(function(){
            if($q(this).val() != ""){
                data.nav[$q(this).val()] = $q(this).parent().find('.aero_nav_to').val();
            }
        });

        //Switch hide dropdown to boolean props
        if(data.nav.length == 0) delete data.nav;
        if(data.hide){ data[data.hide] = true; delete data.hide; }

        //Switch checkboxes to boolean
        data.showTitle = (data.showTitle == "1") ? true : null;
        data.sidebar = (data.sidebar == "1") ? true : null;
        data.multi = (data.multi == "1") ? true : null;
        data.isRestrict = (data.isRestrict == "1") ? true : null;

        //Clean data for empty props
        data = _q.pick(data,_q.identity);

        //Update or create step
        if($q('.aero-editing-step').hasClass('aero-add-step')){
            Aero.step.add(data);
            $q('.aero-helper').remove();
        }else{
            Aero.step.update(index, data);
        }
        Aero.view.admin.close();
    },

    /**
     * Automatically add steps
     * @param path
     */
    autoAdd : function(path){
        var defaults = this.setDefaults(path);

        if(defaults.title == "") defaults.title = "Step " + (defaults.index + 1);
        if(defaults.body == "") defaults.body = "Step Body";
        Aero.step.add(defaults, function(){
        }, true);
    },

    /**
     * Setup step defaults
     * @param path
     * @returns {{id: null, title: string, body: string, url: string, isAdd: boolean, index: *, loc: *, nav: Array, loss: string}}
     */
    setDefaults : function(path){

        var index = (Aero.tip._guide.step.length > 0) ? Aero.tip._current + 1 : 0;
        var nav = [];
        var title = "";
        var body = "";
        var text = "";

        //Auto populate buttons
        var tag = $q(path).prop('tagName').toLowerCase();
        var contains = ['button', 'a'];
        if($q.inArray(tag, contains) > -1){
            text = $q.trim($q(path).text());
            body = "Click " + text;
            title = text;

            nav = { click : "-1" };
        }

        //Auto populate for forms
        contains = ['input', 'select', 'textarea'];
        if($q.inArray(tag, contains) > -1){

            text = $q.trim($q(path).parent().find('label:eq(0)').text());

            var lbl = $q("label[for='"+$q(path).attr('id')+"']");
            if(lbl.length > 0) text = lbl.text().replace('*', '');
            body = "Enter " + text;
            title = text;

            nav = { blur : "-1" };
        }

        var url = document.URL;
        var full = location.protocol+'//'+location.hostname+(location.port ? ':'+location.port: '');
        url = url.replace(full, '');

        //Remove trailing slash and slash with empty #
        if(!url.match(/#\/$/)){
            var sl = /(\/$|\/#$|#$)/;
            url = url.replace(sl, "");
        }

        if (tag == "a"){
            //Auto check page unload
            var ahref = $q(path).attr('href');
            if (ahref != "javascript://" && ahref != "#") nav = { unload:  -1 };
        }

        //Step settings
        var settings = {
            id : null,
            title: title,
            body: body,
            url: url,
            isAdd: true,
            index: index,
            loc : path,
            nav : nav,
            loss : 'ignore',
            showTitle : true
        };

        return settings;
    },

    /**
     *  Picker Button Start
     */
    initPicker : function(isEdit){

        var _this = this;

        Aero.picker.init({
            onStart : function(){
                Aero.tip.hide();
                Aero.view.sidebar.hide();
            },
            callback: function(path){
                Aero.view.sidebar.show(false, 0);
                Aero.picker.destroy();

                var settings = _this.setDefaults(path);

                if(!isEdit) {
                    Aero.view.admin.render("step", $q.extend(Aero.model.step.defaults(), settings));
                }else{
                    $q('.aero-editing').addClass('aero-picking').data('loc', settings.loc);
                    $q('.aero-picking a:eq(1)').trigger('click');
                    $q('.aero-picking').removeClass('.aero-picking');
                }
            }
        });
        Aero.picker.setEvents();
    },

    /**
     *  Create draggable
     */
    renderSortable : function(){

        //Drag order
        $q('.aero-steps ul')
            .sortable({
                placeholder: "aero-helper-dropable",
                update: function(event, ui) {
                    Aero.step.moveIndex(Aero.moveFrom, ui.item.index());
                    Aero.moveFrom = null;
                },
                start: function(event, ui) {
                    Aero.moveFrom = ui.item.index();
                }
            })
            .disableSelection();
    },

    /**
     *  Set event handlers
     */
    setEvents : function(){

        var self = this;

        //Record
        Aero.view.step.record.setEvents();

        //Delete
        $q('body').off("click.aroSD").on("click.aroSD", ".aero-steps ul li a.aero-delete", function(){
            var index = $q( ".aero-steps li" ).index( $q(this).parents('li:eq(0)') );

            Aero.confirm({
                ok : AeroStep.lang.del,
                title : AeroStep.lang.stepdel,
                msg : AeroStep.lang.stepdelconf,
                onConfirm : function(){
                    Aero.step.destroy(index);
                }
            });
            return false;
        });

        //Edit Location
        $q('body').off("click.aeroLocE").on("click.aeroLocE", "a.aero-edit-loc", function(){
            Aero.view.step.admin.initPicker(true);
            return false;
        });

        //Edit
        $q('body').off("click.aeroSEdit").on("click.aeroSEdit", ".aero-steps ul li a.aero-edit", function(){
            var $li = $q(this).parents('li:eq(0)');
            var loc = ($li.data('loc')) ? $li.data('loc') : false;

            Aero.index = $q('.aero-steps li').index($li);

            Aero.view.admin.edit($q(this), loc);
            return false;
        });

        //Save on enter
        $q('body').off("keypress.ssa").on("keypress.ssa", '#editStepFrm input:not(.aero-input-label)', function(e){
            var k = e.keyCode || e.which;
            if(k == '13'){
                if(Aero.model.step.validate()){
                    self.save();
                }
                return false;
            }
        });

        //Save
        $q('body').off("click.aeroSSave").on("click.aeroSSave", ".aero-editing-step .aero-btn-save", function(){
            if(Aero.model.step.validate()){
                self.save();
                return false;
            }
        });

        //Aero Tabs
        $q('body').off("click.aeroTab").on("click.aeroTab", ".aero-tab a", function(){
            var activeC = 'aero-tab-active';
            var $section = $q(this).parents('.aero-section');

            $section.find('.' + activeC).removeClass(activeC);
            $section.find($q(this).attr('href')).addClass(activeC);
            $q(this).addClass(activeC);

            if($q(this).attr('id')=="aero-section-quiz")
                Aero.view.step.admin.quiz.render();

            return false;
        });

        //Add nav
        $q('body').off("click.aeroNAdd").on("click.aeroNAdd", ".aero-editing-step .aero-add-nav", function(){
            self.addNav();
            return false;
        });

        //Remove nav
        $q('body').off("click.aeroNDel").on("click.aeroNDel", ".aero-editing-step .aero-del-nav", function(){
            self.destroyNav($q(this));
            return false;
        });

        //Change size
        $q('body').off("change.ssC").on("change.ssC", ".aero-section select[name='aero_size']", function(){
            $q('.aero-custom-size').hide();
            $q('.aero-custom-size input').val("");

            if($q(this).val() == "custom") $q('.aero-custom-size').show();
        });

        //Change exception type
        $q('body').off("change.seC").on("change.seC", ".aero-section select[name='aero_miss']", function(){
            $q('.aero-alert-edit, .aero-skip-edit').hide();
            $q('input[name="aero_alert"], input[name="aero_alertContent"]').val("");
            if($q(this).val() == "alert") $q('.aero-alert-edit').show();
            if($q(this).val() == "skipto") $q('.aero-skip-edit').show();
        });

        //Change exception type
        $q('body').off("change.seL").on("change.seL", ".aero-section select[name='aero_loss']", function(){
            $q('.aero-loss-alert-edit, .aero-loss-skip-edit').hide();
            $q('input[name="aero_lossalert"], input[name="aero_lossalertContent"]').val("");
            if($q(this).val() == "alert") $q('.aero-loss-alert-edit').show();
            if($q(this).val() == "skipto") $q('.aero-loss-skip-edit').show();
        });

        //Change exception type
        $q('body').off("change.lut").on("change.lut", ".aero-section input[name='aero_locText']", function(){

            var query, nQuery, $el, tag, bits;

            query = $q("input[name='aero_loc']").val();
            $el = $q( query );
            tag = $el.prop("tagName");

            //Check to see if we have a button or anchor
            if(tag != "A" && tag != "BUTTON" && tag != "LABEL") return;

            //Break up current query
            bits = query.split(">");
            bits = bits.slice(0, -1);

            if($q(this).is(':checked')){
                nQuery = bits.join(">") + "> "+ tag.toLowerCase() + ":contains('" + $el.text().trim() + "')";
            }else{
                nQuery = bits.join(">") + "> "+ tag.toLowerCase() + ":eq(" + $q(bits.join(">") + "> "+ tag.toLowerCase()).index($el) + ")";
            }

            $q("input[name='aero_loc']").val(nQuery);
        });

        //Add step
        $q('body')
            .on("mouseup", "a.aero-btn-picker", function() {
                $q('.aero-play').remove();
                self.initPicker();
            })
            .on("click", "a.aero-btn-picker", function() {
                return false;
            });
    }
};

/**
 * Record steps from user interactions
 * @type {{init: Function, setEvents: Function}}
 */
Aero.view.step.record = {

    init : function(e){
        var path = Aero.picker.get(e);
        Aero.view.step.admin.autoAdd(path);

        $q('.aero-tip').remove();
    },

    /**
     *  Turn recording on
     */
    on : function(ignorePlay){

        var _this = this;
        if(!Aero.tip._guide) return;
        aeroStorage.setItem('aero:session:recording', 1);
        $q('.aero-btn-record span:eq(0)').addClass('recording');

        Aero.log('Started Recording...', 'warn');

        //Cleanup
        if(!ignorePlay) {
            $q('.aero-play').fadeOut(500, function () {
                $q('.aero-play').remove();
            });
        }
        $q('.aero-tip').remove();

        $q('body').off('mousedown.autorc').on('mousedown.autorc', function (e) {
            _this.init(e);
        });

        $q('body').off('input.autori,textarea.autort').on('focus', 'input.autori,textarea.autort', function (e) {
            _this.init(e);
        });

        //Don't add on aero
        $q('body').on('mousedown.side', '.aero-modal, .aero-tip, .aero-app, .ae-active-el, .aero-play', function (e) {
            e.stopPropagation();
        });

    },

    /**
     *  Turn recording off
     */
    off : function(){
        Aero.log('Stopped Recording.', 'success');
        aeroStorage.removeItem('aero:session:recording');
        $q('.aero-btn-record span:eq(0)').removeClass('recording');
        $q('body').off('mousedown.autorc input.autori textarea.autort');
    },

    /**
     *  Set UI Events
     */
    setEvents : function(){

        var _this = this;

        //Record button
        $q('body').off('click.aerec').on('click.aerec', '.aero-btn-record', function(){
            if(!aeroStorage.getItem('aero:session:recording')) {
                _this.on();
            }else{
                _this.off();
            }}
        );
    }
}
