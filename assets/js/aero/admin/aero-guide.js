"use strict";

/**
 * Common Admin Guide Sidebar View
 * @author Mike Priest
 * @type {{id: string, renderRoles: Function, renderPathways: Function, renderMapping: Function, save: Function, setEvents: Function}}
 */
Aero.view.guide.admin = {

    id : "",

    /**
     *  Render role screen
     */
    renderRoles : function(){
        this.renderMapping('roles');
    },

    /**
     *  Render pathway screen
     */
    renderPathways : function(){
        this.renderMapping('pathways');
    },

    /**
     *  Render mapping between guide and pathway/role
     */
    renderMapping : function(type){

        //Show available
        Aero[type].getAll(function(result){

            $q('.aero-' + type).html("").append('<option value="">- Select a '+type.slice(0, -1)+' -</option>');
            for(var i in result){
                if (result.hasOwnProperty(i)) {
                    $q('.aero-' + type).append('<option value="' + result[i].id + '">' + result[i].title + '</option>');
                }
            }
        });

        //Show currently assigned
        Aero[type].getByGuideid(Aero.view.admin.guideid, function(result){
            $q('.aero-'+type+'-list ul').html('');
            for(var i in result){
                if (result.hasOwnProperty(i)) {
                    $q('.aero-'+type+'-list ul').append('<li class="clearfix" data-id="'+result[i].id+'"><a class="aero-btn-grey"><i class="fa fa-times"></i></a><span>'+result[i].title+'</span></li>');
                }
            }
        });
    },

    /**
     *  Save form
     */
    save : function(){

        var id = Aero.view.admin.guideid;
        var data = $q('#editGuideFrm').aeroSerialize();
        data.desc = $q('#aeroEditor').trumbowyg('html');

        Aero.view.admin.close();

        //Switch checkboxes to boolean
        data.active = (data.active == "1");
        data.auto = (data.auto == "1") ? true : null;
        data.autoPage = (data.autoPage == "1") ? true : null;

        //Update or create
        if(id != ""){
            data.id = id;
            Aero.guide.update(data);
        }else{
            Aero.guide.create(data, function(r){
                Aero.tip.start(r);
            });
        }
    },

    /**
     *  Set event handlers
     */
    setEvents : function(guide){

        var self = this;

        //Create
        $q('body').off("click.aeroGCreate").on("click.aeroGCreate", "a.aero-btn-create", function(){

            //Clear out guideid
            Aero.view.admin.guideid = "";
            Aero.view.admin.render("guide", {});
            return false;
        });

        //Menu
        $q('body').off("click.aeroSMenu").on("click.aeroSMenu", ".aero-strip-menu a", function(){
            var $li = $q(this).parents('li:eq(0)');
            Aero.view.admin.renderMenu($li);
            return false;
        });
        
        //Delete
        $q('body').off("click.aeroGDelete").on("click.aeroGDelete", ".aero-guides ul li a.aero-delete", function(){
            var li = $q(this).parents('li:eq(0)');
            var id = li.data('guideid');
            var name = li.find('a:last').text();

            Aero.confirm({
                ok : AeroStep.lang.del,
                title : name,
                msg : AeroStep.lang.guidedelconf,
                onConfirm : function(){
                    Aero.guide.destroy(id);
                }
            });

            return false;
        });

        //Edit
        $q('body').off("click.aeroGEdit").on("click.aeroGEdit", ".aero-guides ul li a.aero-edit", function(){
            Aero.view.admin.edit($q(this));
            return false;
        });

        //Save on enter
        $q('body').off("keypress.gsa").on("keypress.gsa", '#editGuideFrm input', function(e){
            var k = e.keyCode || e.which;
            if(k == '13'){
                if(Aero.model.guide.validate()){
                    self.save();
                }
                return false;
            }
        });

        //Save
        $q('body').off("click.aeroGSave").on("click.aeroGSave", ".aero-editing-guide .aero-btn-save", function(){
            if(Aero.model.guide.validate()){
                self.save();
                return false;
            }
        });

        //Create pathway association
        $q('body').off("click.apA").on("click.apA", ".aero-pathways-search a", function(){
            var id = $q('.aero-pathways').val();
            if(id != "") Aero.pathways.createMapping(id, Aero.view.admin.guideid);
            return false;
        });

        //Delete pathway association
        $q('body').off("click.dpA").on("click.dpA", ".aero-pathways-list a", function(){
            var id = $q(this).parent().data('id');

            Aero.confirm({
                ok : "unlink",
                title : "Removing pathway link",
                msg : "Are you sure you want to remove this pathway?",
                onConfirm : function(){
                    Aero.pathways.destroyMapping(id, Aero.view.admin.guideid);
                    return false;
                }
            });
            return false;
        });

        //Create role association
        $q('body').off("click.arA").on("click.arA", ".aero-roles-search a", function(){
            var id = $q('.aero-roles').val();
            if(id != "") Aero.roles.createMapping(id, Aero.view.admin.guideid);
            return false;
        });

        //Change restrict
        $q('body').off("click.arcs").on("click.arcs", ".aero-section input[name='aero_isRestrict']", function(){
            $q('.aero-color-wrapper').hide();
            if($q(this).is(':checked')) $q('.aero-color-wrapper').show();
        });

        //Delete role association
        $q('body').off("click.drA").on("click.drA", ".aero-roles-list a", function(){
            var id = $q(this).parent().data('id');

            Aero.confirm({
                ok : "unlink",
                title : "Removing role link",
                msg : "Are you sure you want to remove this role?",
                onConfirm : function(){
                    Aero.roles.destroyMapping(id, Aero.view.admin.guideid);
                    return false;
                }
            });
            return false;
        });
    }
};
