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
		<div class="small-offset-1 small-10 medium-offset-2 medium-8 large-offset-4 large-4 columns panel">
		 	<div class="small-6 medium-4 large-4 center">
		 		<img src="<?= $baseUrl; ?>assets/images/jumpseat-stacked-1day.png" />
            </div>
            <div class="large-9 center">
                <p><?= $lang->tagline; ?></p>
            </div>
        </div>
     </div>
     <div class="row">
         <div class="login-form small-offset-1 small-10 medium-offset-2 medium-8 large-offset-4 large-4 columns form">
            <form class="login">
                <input type="text" value="" name="username" placeholder="<?= $lang->luser; ?>" />
                <input type="password" value="" name="password" placeholder="<?= $lang->lpass; ?>" />
                <a href="/apps" class="btn sign-in"><?= $lang->signin; ?></a>
            </form>

            <form class="email-form clearfix">
                <input id="reset" type="text" value="" name="email" placeholder="<?= $lang->luser; ?>" />
                <a class="reset-pwd btn"><?= $lang->reset; ?></a>
                <a class="reset-cnl btn"><?= $lang->cancel; ?></a>
            </form>

            <div class="login reset-password">
                <a href="#"><?= $lang->passreset; ?></a>
            </div>
        </div>
    </div>
</div>


<script src="<?= $baseUrl; ?>assets/js/third_party/jquery.js"></script>
<script src="<?= $baseUrl; ?>assets/js/third_party/underscore.js"></script>
<script src="<?= $baseUrl; ?>assets/lib/foundation/js/vendor/placeholder.js"></script>
<script src="<?= $baseUrl; ?>assets/lib/foundation/js/foundation.min.js"></script>
<script src="<?= $baseUrl; ?>assets/js/views/login<?= MIN ?>.js"></script>
<script>
	$q(document).foundation();
	$q('input, textarea, select').placeholder();
</script>
</body>
</html>
