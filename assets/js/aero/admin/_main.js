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
            if (event.shiftKey) Aero.view.step.admin.initPicker();
        }
    });
}

//Start
AeroAdminMain();
