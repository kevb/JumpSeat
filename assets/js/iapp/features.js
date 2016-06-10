"use strict";

/**
 * @class Feature
 * Start admin namespace
 */
var Feature = {

	//Id used for edit
	id : null,

	init : function(){
		$q('#form-wrapper').html("");
		Feature.view.render();

		//New Menu
		var options = {
				onDelete : function(ids){
					if(ids.length > 0){
						//Confirm
						Aero.confirm({
							ok : "Delete",
							title : "Deleting",
							msg : "You are about to delete <strong>"+ ids.length +"</strong> item(s)</strong>?",
							onConfirm : function(){
								Feature.api.del(ids);
							}
						});
					}
				}
		};
		new Utils.menu("#menu", options);
	}
};


/**
 *  @namespace Feature
 *  @class Feature.model
 *  Model for Feature Object
 */
Feature.model = {

	url : "api/features",

	defaults : function(){
		return {
			"title" : "",
			"description" : ""
		}
	},

	validate : function(){
		//Check for required
		return $q('form').isFormValid();
	},

	save : function(){

		//Get form data
		var data = $q('form').aeroSerialize();
			data.active = (data.active) ? true : false;

		//Create or update
		if(Feature.id){
			Feature.api.update(data, Feature.id);
		}else{
			Feature.api.create(data);
		}
	}
};

/**
 *  @namespace Feature
 *  @class Feature.view
 *  UX Views and Event Handlers
 */
Feature.view = {

	table : null,

	/**
	 *  Render list view
	 */
	render : function(){

		var url = "features/table";
		var columns = [
		       { "width": "5%",  "sClass" : "center" },
		       { "width": "25%" },
		       { "width": "25%" },
               { "width": "10%", "sClass" : "center" },
		       { "width": "20%"},
		       { "width": "5%", "sClass" : "tright"}
		 ];

		this.table = new Utils.datatable(url, columns);
		this.setEvents();
	},

	/**
	 *  Render list view
	 */
	renderForm : function(feature){

		Aero.tpl.get("feature-form.html", function(r){

			Feature.id = (feature) ? feature.id : null;
			if(!feature) feature = {};

			//Get load tpl
			var tpl = _q.template(r);

			//Confirm
			Aero.confirm({
				ok : "save",
				title : "Feature Information",
				msg : tpl( { feature: feature }),
				onValidate : function(){
					return Feature.model.validate();
				},
				onConfirm : function(){
					Feature.model.save();
				}
			});
		});
	},


	/**
	 *  Setup all event triggers
	 */
	setEvents : function(){

		//Shortcuts
		$q('body').off("keypress.sn").on("keypress.sn", function(e){
			var k = e.keyCode || e.which;
			if(e.target.tagName == "BODY" && k == '78' && e.shiftKey) Feature.view.renderForm();
		});

		//Add
		$q('.add').off("click.patha").on("click.patha", function(){
			Feature.view.renderForm();
			return false;
		});

		//Delete
		$q('body').off("click.pathd").on("click.pathd", ".delete", function(){
			var id = $q(this).parents('div:eq(0)').data('id');
			var name = $q(this).parents('tr').find('td:eq(1)').text();
			//Confirm
			Aero.confirm({
				ok : "Delete",
				title : name,
				msg : "Are you sure you want to delete this?",
				onConfirm : function(){
					Feature.api.del(id);
				}
			});
			return false;
		});

		//Edit
		$q('body').off("click.pathe").on("click.pathe", ".edit", function(){
			var id = $q(this).parents('div:eq(0)').data('id');

			Feature.api.get(function(r){
				Feature.view.renderForm(r);
			}, id);
			return false;
		});

        //Trash
        $q('body').off("click.pathf").on("click.pathf", ".trash", function(){
            window.location.href = '/app/' + AeroStep.host + '/trash';
            return false;
        });
	}
};


/**
 *  @namespace Feature
 *  @class Feature.api
 *  API REST Services
 */
Feature.api = {

	/**
	 *  Get all or by id
	 *  @param function callback
	 *  @param id
	 */
	get : function(callback, id){
		//Get by id?
		var data = {};
		if(id) data = { id : id };

		Aero.send(Feature.model.url, data, function(r){
			if(callback) callback(r);
		}, "GET");
	},

	/**
	 *  Create new feature
	 *  @param function callback
	 *  @param id
	 */
	create : function(data){
		//Call
		Aero.send(Feature.model.url, data, function(){
			Feature.view.table.ajax.reload();
		}, "POST");
	},


	/**
	 *  Clone
	 *  @param function callback
	 *  @param id
	 */
	clone : function(id){
		//Call
		Aero.send(Feature.model.url + "/clone", { id : id }, function(){
			Feature.view.table.ajax.reload();
		}, "POST");
	},

	/**
	 *  Update feature data
	 *  @param string[] data
	 *  @param function callback
	 */
	update : function(data, id, callback){

		data.id = id;

		//Call
		Aero.send(Feature.model.url, data, function(r){
			Feature.view.table.ajax.reload();
			if(callback) callback(r);
		}, "PUT");
	},

	/**
	 *  Delete feature
	 *  @param string id
	 */
	del : function(id){
		//Call
		Aero.send(Feature.model.url, { id : id}, function(){
			Feature.view.table.ajax.reload();
		}, "DELETE");
	}
};

$q(function(){
	Feature.init();
});
