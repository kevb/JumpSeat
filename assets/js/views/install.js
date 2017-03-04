"use strict";

var User = {};
/**
 *  @namespace User
 *  @class User.model
 *  Model for User Object
 */
User.model = {

    url : "/api/users/install",

    validate : function(){
        //Check for required
        var error = [];
        var valid = $q('form').isFormValid();
        var username = $q('input[name="email"]');
        var password = $q('input[name="password"]');
        var passwordv = $q('.passwordv').val();

        if(!valid){
            error.push('Please check required fields');
        }

        if(password.val() != "" && $q('.password-verdict').text() == "Weak"){
            valid = false;
            error.push('Password is too weak');
        }

        if(password.val() != passwordv){
            valid = false;
            error.push('Password and Confirm Password don\'t match');
        }

        if(!valid){
            //Already error box?
            if($q('.aero-error-box').length == 0) $q('form').prepend('<div class="aero-error-box"><ul></ul></div>');

            for(var i in error){
                $q('.aero-error-box ul').append('<li>'+error[i]+'</li>');
            }
        }

        return valid;
    },

    save : function(){

        //Get form data
        var data = $q('form').aeroSerialize();

        if(this.validate(data)){
            User.api.create(data);
        }
    }
};

/**
 *  @namespace User
 *  @class User.view
 *  UX Views and Event Handlers
 */
User.view = {

    /**
     *  Setup all event triggers
     */
    setEvents : function(){

        var options = {
            onKeyUp: function (evt) {}
        };
        $q('input[name="password"]').pwstrength(options);
        $q('[name=email]').focus();

        $q('body').on("keypress", function(e){
            var k = e.keyCode || e.which;
            if(k != '13') return;
            User.model.save();
        });

        $q('.sign-in').on("click", function(e){
            User.model.save();
            return false;
        });
    }
};


/**
 *  @namespace User
 *  @class User.api
 *  API REST Services
 */
User.api = {

    /**
     *  Update user data
     *  @param string[] data
     *  @param function callback
     */
    create : function(data){

        $q.post(User.model.url, data, function( r ) {
            if(r.success){
                window.location = "/apps";
            }else{
                $q('form').prepend(r.error);
            }
        });
    }
};


//Form Validate
$q.fn.isFormValid = function()
{
    var errors = [];

    //Clear
    $q('.aero-require-error').removeClass('aero-require-error');
    $q('.aero-error-box').remove();

    $q(this).find('.aero-required').each(function(){
        //Clear error
        $q(this).removeClass('aero-require-error');

        //Check error
        if($q(this).val() == ""){
            if(errors.length == 0) errors.push("Please check required fields");
            $q(this).addClass('aero-require-error');
        }
    });

    if(errors.length > 0){
        if($q('.aero-modal form:eq(0)').length > 0) $q('.aero-modal form:eq(0)').prepend('<div class="aero-error-box"><ul><li>'+errors.join('</li><li>')+'</li></ul></div>');
        return false;
    }

    return true;
};

//$q Utilies
$q.fn.aeroSerialize = function()
{
    var o = {};
    var a = this.serializeArray();

    $q.each(a, function() {
        var name = this.name.replace("aero_", "");

        if (o[name] !== undefined) {
            if (!o[name].push) {
                o[name] = [o[this.name]];
            }
            o[name].push(this.value || '');
        } else {
            o[name] = this.value || '';
        }
    });
    return o;
};

$q(function(){
    User.view.setEvents();
});

/**
 * Set up listeners
 */
$q("img").on("load", function() {
    $q('.jumbo')
        .css('background-image', 'url("/assets/images/jumbo-image.png")')
        .fadeIn(500)
        .animate({ 'right' : 90 }, 2000, 'swing');
}).each(function() {
    if(this.complete) $q(this).load();
});
