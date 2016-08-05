"use strict";

/**
 *  Start the role mapping to pathways
 */
require(['api/guides', 'api/languages'], function(){

    /**
     *  @namespace Version
     *  @class Version.view
     *  UX Views and Event Handlers
     */
    Language.view = {

        table: null,

        /**
         *  Initialize Languages
         */
        init: function () {
            Language.id = $q('#language').data('guideid');
            Language.view.render();
        },

        /**
         *  Render list view
         */
        render: function () {

            var langs = [];
            var columns = [];

            //Start main lang
            Guide.api.get(function(guide){

                langs.push(guide.locale);

                var column = [];
                for(var i in guide['step']){

                    var title = guide['step'][i]['title'];
                    var body = guide['step'][i]['body'];
                    var content = title + "<br>" + body;

                    $q('#languages tbody').append('<tr><td>'+(parseInt(i)+1)+'</td><td style="width:400px">' + content  + '</td></tr>');
                }

            }, Language.id);


            //Language packs
            Language.api.get(function(packs){
                for(var i in packs){
                    langs.push(packs[i].lang);

                    for(var j in packs[i]['step']){

                        var title = packs[i]['step'][j]['title'];
                        var body = packs[i]['step'][j]['body'];
                        var content = title + "<br>" + body;

                        $q('#languages tbody tr:eq('+j+')').append('<td style="width:400px">' + content  + '</td>');
                    }
                }
            });

            for(var i in langs) {
                $q('#languages thead tr').append('<th>' + langs[i] + '</th>');
            }

            this.setEvents();
        },

        /**
         *  Setup all event triggers
         */
        setEvents: function () {
        }
    };

    $q(function () {
        Language.view.init();
    });
});
