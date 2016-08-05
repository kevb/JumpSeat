"use strict";

/**
 * @class User
 * Start admin namespace
 */
var User = {

    //Id used for edit
    id : null
};


/**
 *  @namespace User
 *  @class User.model
 *  Model for User Object
 */ 
User.model = {

    url : "api/users",

    defaults : function(){
        return {
            "title" : "",
            "description" : ""
        }
    },

    validate : function(){

        //Check for required
        var error = [];
        var valid = $q('form').isFormValid();
        var password = $q('input[name="password"]');
        var passwordv = $q('.passwordv');

        if(password.val() != "" && $q('.password-verdict').text() == "Weak"){
            valid = false;
            password.addClass('aero-require-error');
            error.push('Password is too weak');
        }

        if(password.val() != passwordv.val()){
            valid = false;
            passwordv.addClass('aero-require-error');
            error.push('Password and Confirm Password don\'t match');
        }

        if(!valid){
            //Already error box?
            if($q('.aero-error-box').length == 0) $q('form').prepend('<div class="aero-error-box"><ul></ul></div>');

            for(var i in error){
                $q('.aero-error-box ul').append('<li>'+error[i]+'</li>');
            }

            if($q('#panel2-1 .aero-require-error').length == 0) $q('.tabs li:eq(1) a').click();
        }

        return valid;
    },

    save : function(){

        //Get form data
        var data = $q('form').aeroSerialize();
        data.sysadmin = (data.sysadmin) ? true : false;

        //Create or update
        if(User.id){
            User.api.update(data, User.id);
        }else{
            User.api.create(data);
        }
    }
};

/**
 *  @namespace User
 *  @class User.api
 *  API REST Services
 */
User.api = {

    /**
     *  Get all or by id
     *  @param function callback
     *  @param id
     */
    get : function(callback, id){
        //Get by id?
        var data = {};
        if(id) data = { id : id };

        Aero.send(User.model.url, data, function(r){
            if(callback) callback(r);
        }, "GET");
    },

    /**
     *  Create new user
     *  @param function callback
     *  @param id
     */
    create : function(data){
        //Call
        Aero.send(User.model.url, data, function(){
            User.view.table.ajax.reload();
        }, "POST");
    },


    /**
     *  Clone
     *  @param function callback
     *  @param id
     */
    clone : function(id){
        //Call
        Aero.send(User.model.url + "/clone", { id : id }, function(){
            User.view.table.ajax.reload();
        }, "POST");
    },

    /**
     *  Update user data
     *  @param string[] data
     *  @param function callback
     */
    update : function(data, id, callback){

        data.id = id;

        //Call
        Aero.send(User.model.url, data, function(r){
            User.view.table.ajax.reload();
            if(callback) callback(r);
        }, "PUT");
    },

    /**
     *  Delete user
     *  @param string id
     */
    del : function(id){
        //Call
        Aero.send(User.model.url, { id : id}, function(){
            User.view.table.ajax.reload();
        }, "DELETE");
    }
};
