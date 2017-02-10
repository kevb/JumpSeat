/**
 *  User login API
 */
function login(){
	$q('.error').remove();
    clearBox();

    var error = '<div class="alert-box error">Invalid username or password</div>';
    var locked = '<div class="alert-box error">Your account has been locked. Please reset your password.</div>';
	var data = {
		username: $q('input[name="username"]').val().toLowerCase(),
		password: $q('input[name="password"]').val()
	};

    if(data.username == ""){
        $q('form').prepend(error);
        return false;
    }

	$q.post("/api/login", data, function( r ) {
		if(r.success == 0){
			window.location = "/apps";
		}else if (r.success == 1){
			$q('form').prepend(error);
		}else {
            $q('form').prepend(locked);
        }
	});
}

/**
 *  Reset password
 */
function password_reset(){
    var email = $q('input[name="email"]').val();
    var data = { email: email };
    var error = '<div class="alert-box error">Email address not found.</div>';
    var success = '<div class="alert-box success">Success! Please check your email for your reset password link.</div>';
    var waiting = '<div class="alert-box info">Please wait while we find your account...</div>';
    var valid = true;

    //clear
    clearBox();

    //Required
    if(email == "") {
        $q('form').prepend(error);
        return false;
    }

    $q('form').prepend(waiting);

    //Send user information
    $q.get('/api/users/password_reset', data, function (r) {

        $q('.info').remove();
        if (r.sent) {
            $q('.email-form').hide();
            $q('.login').show();
            $q('form').prepend(success);
        } else {
            $q('form').prepend(error);
        }
    })
    .fail(function () {
        $q('.info').remove();
        $q('form').prepend(error);
    });
}

function getURLParameter(sParam)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) {
            return sParameterName[1];
        }
    }
};

function clearBox(){
    $q('.alert-box').hide();
}

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

$q(function(){
	$q('[name=username]').focus();

	$q('body').on("keypress", function(e){
		var k = e.keyCode || e.which;
		if(k != '13') return;
		login();
	});

	$q('.sign-in').on("click", function(e){
        login();
		return false;
	});

    $q('.reset-password > a').on('click', function(e){
        clearBox();
        $q('.login').hide();
        $q('.email-form').show();
    });

    $q('.reset-pwd').on('click', function(e){
        password_reset();
        return false;
    });

    $q('#reset').on("keydown", function(e){
        var k = e.keyCode || e.which;
        if(k != '13') return;

        password_reset();
        return false;
    });

    $q('.reset-cnl').on('click', function(e) {
        clearBox();
        $q('.email-form').hide();
        $q('.login').show();
    });

    //Display error message
    if (getURLParameter('error')){
        $q('form').prepend('<div class="alert-box error">Link is invalid or expired.</div>');
    }
});
