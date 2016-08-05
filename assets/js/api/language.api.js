"use strict";

/**
 * @class Language
 * Start admin namespace
 */
var Language = {

    //Id used for edit
    id : null
};


/**
 *  @namespace Language
 *  @class Language.model
 *  Model for Language Object
 */
Language.model = {

    url : "api/language",

    validate : function(){
        //Check for required
        return $q('form').isFormValid();
    },

    save : function(){

        //Get form data
        var data = $q('form').aeroSerialize();
            data.active = (data.active) ? true : false;

        //Create or update
        if(Language.id){
            Language.api.update(data, Language.id);
        }else{
            Language.api.create(data);
        }
    }
};


/**
 *  @namespace Language
 *  @class Language.api
 *  API REST Services
 */
Language.api = {

    /**
     *  Get all or by id
     *  @param function callback
     *  @param id
     */
    get : function(callback){
        //Get by id?
        var data = {
            guideid : Language.id
        };

        Aero.send(Language.model.url, data, function(r){
            if(callback) callback(r);
        }, "GET");
    },

    /**
     *  Create new language
     *  @param function callback
     *  @param id
     */
    create : function(data){
        //Call
        Aero.send(Language.model.url, data, function(){
            Language.view.table.ajax.reload();
        }, "POST");
    },

    /**
     *  Update language data
     *  @param string[] data
     *  @param function callback
     */
    update : function(data, id, callback){

        data.id = id;

        //Call
        Aero.send(Language.model.url, data, function(r){
            Language.view.table.ajax.reload();
            if(callback) callback(r);
        }, "PUT");
    },

    /**
     *  Delete language
     *  @param string id
     */
    del : function(id){
        //Call
        Aero.send(Language.model.url, { id : id}, function(){

        }, "DELETE");
    }
};
