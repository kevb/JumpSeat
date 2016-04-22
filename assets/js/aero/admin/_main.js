"use strict";

/**
 *  Main() function file to setup admin
 *  @author Mike Priest
 */
function AeroAdminMain() {

    //If admin, init
    if (AeroStep.admin) {
        //Setup events
        Aero.view.admin.setEvents("step");
        Aero.view.admin.setEvents("guide");
        Aero.view.step.admin.setEvents();
        Aero.view.guide.admin.setEvents();
    }

    //Shortcuts
    $q(document).keyup(function (e) {
        if (e.keyCode == 192) {
            if(event.shiftKey) Aero.view.step.admin.initPicker();
        }

        if (e.keyCode == 82) {
            if(event.shiftKey){
                $q('body').on('mousedown', function(e){
                    var path = Aero.picker.get(e);
                    Aero.view.step.admin.autoAdd(path);
                });

                //$q('body').on('focus', 'input,textarea', function(e){
                //    var path = Aero.picker.get(e);
                //    Aero.view.step.admin.autoAdd(path);
                //});
                //
                ////Don't add on aero
                //$q('body').on('mousedown.side', '.aero-app', function(e){ e.stopPropagation(); });
            }
        }
    });
}

//Start
AeroAdminMain();
