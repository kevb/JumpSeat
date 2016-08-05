<!doctype html>
<html class="no-js" lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>JumpSeat | <?= $lang->guides; ?></title>
	<link rel="stylesheet" href="<?= $baseUrl; ?>assets/lib/foundation/css/foundation.css" />
	<link rel="stylesheet" href="<?= $baseUrl; ?>assets/css/aero-admin.css" />
	<link rel="stylesheet" href="<?= $baseUrl; ?>assets/css/ss-junior/webfonts/ss-junior.css" />
	<script src="<?= $baseUrl; ?>assets/lib/foundation/js/vendor/modernizr.js"></script>
	<link rel="shortcut icon" href="/assets/images/favicon.ico" />
	<link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
<? include 'inc/sidebar.php'; ?>
<? include 'inc/header.php'; ?>
<div id="screenGuide" class="balloon">
	<div class="row">
		<div class="large-8 columns" style="margin-bottom:2em;">
			<h4 id="language" data-guideid="<?= $id ?>"><?= $lang->versions; ?></h4>
		   	<p><?= $lang->versionsd; ?></p>
		</div>
	</div>
	<div class="row">
		<div id="guideGroup" class="large-12 columns">

            <div class="test"></div>

            <div style="width:500px; overflow-x:auto">
                <table id="languages" style="display: block;overflow-x: auto; table-layout:fixed">
                    <thead>
                        <tr>
                            <th>Steps</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
      	</div>
    </div>
</div>
<? include 'inc/footer.php'; ?>
<script src="<?= $baseUrl; ?>assets/js/views/language<?= MIN ?>.js"></script>
</body>
</html>
