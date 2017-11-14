<!doctype html>
<html class="no-js" lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>JumpSeat | Login</title>
	<link rel="stylesheet" href="<?= $baseUrl; ?>assets/lib/foundation/css/foundation.css" />
	<link rel="stylesheet" href="<?= $baseUrl; ?>assets/css/login.css" />
	<script src="<?= $baseUrl; ?>assets/lib/foundation/js/vendor/modernizr.js"></script>
	<link rel="shortcut icon" href="/assets/images/favicon.ico" />
	<link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
<div class="jumbo-wrapper">
	<div class="jumbo" style="display:none">
		<img src="/assets/images/jumbo-image.png" style="display:none" />
	</div>
</div>

<div id="login">
	<div class="row">
		<div class="small-offset-1 small-10 medium-offset-2 medium-8 large-offset-4 large-4 columns panel" style="margin-top:100px;">
		 	<div class="small-6 medium-4 large-4 center">
		 		<img src="<?= $baseUrl; ?>assets/images/jumpseat-stacked.png" />
            </div>
            <div class="large-9 center">
                <p><?= $lang->installgood; ?></p>
            </div>
        </div>
     </div>
     <div class="row">
         <div class="login-form small-offset-1 small-10 medium-offset-2 medium-8 large-offset-4 large-4 columns form">
            <form class="login install">
                <p><?= $lang->installdesc; ?></p>

                <div class="row">
                    <div class="large-6 columns">
                        <input type="text" class="aero-required" value="" name="firstname" placeholder="<?= $lang->firstname; ?>" />
                    </div>
                    <div class="large-6 columns">
                        <input type="text" class="aero-required" value="" name="lastname" placeholder="<?= $lang->lastname; ?>" />
                    </div>
                </div>

                <input type="text" class="aero-required" value="" name="email" placeholder="<?= $lang->emailaddress; ?>" />

                <input name="password" type="password" class="aero-required" placeholder="<?= $lang->password; ?>" value="">
                <input type="password" class="passwordv aero-required" placeholder="<?= $lang->passwordvp; ?>" value="">
                <a href="/apps" class="btn sign-in"><?= $lang->signin; ?></a>
            </form>
        </div>
    </div>
</div>


<script src="<?= $baseUrl; ?>assets/js/third_party/jquery.js"></script>
<script src="<?= $baseUrl; ?>assets/js/third_party/underscore.js"></script>
<script src="<?= $baseUrl; ?>assets/lib/foundation/js/vendor/placeholder.js"></script>
<script src="<?= $baseUrl; ?>assets/lib/foundation/js/foundation.min.js"></script>
<script src="<?= $baseUrl; ?>assets/js/views/install<?= MIN ?>.js"></script>
<script src="/assets/js/third_party/password.js"></script>
<script>
	$q(document).foundation();
	$q('input, textarea, select').placeholder();
</script>
</body>
</html>
