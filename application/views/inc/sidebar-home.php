<? $url = $_SERVER['REQUEST_URI'];
   $users = (strpos($url, 'users') !== false) ? true : false;
?>
<div id="sidebar">
	<ul>
		<li class="<?= $users ? "": "active"; ?> home"><a data-title="<?= $lang->home; ?>" href="/apps"><span></span></a></li>
		<? if($_SESSION['sysadmin']){ ?><li class="<?= $users ? "active": ""; ?> roles"><a data-title="<?= $lang->users; ?>" href="/app/users"><span></span></a></li><? } ?>
	</ul>
</div>
