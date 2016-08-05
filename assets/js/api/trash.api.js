/**
 * @class Guide
 * Start admin namespace
 */
var Trash = {};

/**
 *  @namespace Trash
 *  @class Trash.model
 *  Model for Trash Object
 */
Trash.model = {

    url : "api/trash",
    
    defaults : function(){
        return {
            "title" : "",
            "description" : ""
        }
    }
};

/**
 *  @namespace Trash
 *  @class Trash.api
 *  API REST Services
 */
Trash.api = {

    /**
     *  Retrieve the guides in the trash
     *  @param function callback
     *  @param id
     */
    get: function (callback) {

        var data = {};

        Aero.send(Trash.model.url, data, function (r) {
            if (callback) callback(r);
        }, "GET");
    },

    restore: function (id, callback) {
        var data = {id: id};

        Aero.send(Trash.model.url, data, function (r) {
            if (callback) callback(r);
        }, 'POST');
    },

    /**
     *  Delete guide
     *  @param string id
     */
    del: function (id) {
        //Call
        Aero.send(Trash.model.url, {id: id}, function () {
            Trash.view.table.ajax.reload();
        }, "DELETE");
    }
};
