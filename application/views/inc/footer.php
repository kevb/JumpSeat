<div class="footer">
    <?= $lang->copy . $this->config->item('version') ?> <?= $_SESSION['license'] ?>
</div>

<script type="application/javascript">
    var AeroStep = {
        baseUrl : '<?= $baseUrl; ?>',
        lang : <?= json_encode($lang); ?>,
        host : '<?= isset($host) ? $host : "" ?>',
        getUsername : function(){
            return '<?= $username ?>';
        },
        debug : <?= ENVIRONMENT == 'development' ? "true":"false" ?>,
        min : '<?= MIN ?>'
    };
</script>

<!-- NEED THIS EXTRA JQUERY :S -->
<script src="<?= $baseUrl; ?>assets/js/third_party/jquery-10.2.js"></script>
<script src="<?= $baseUrl; ?>assets/js/third_party/datatable/datatable.js"></script>
<script src="<?= $baseUrl; ?>assets/js/third_party/require/require.min.js"></script>
<script src="<?= $baseUrl; ?>assets/js/views/_require<?= MIN ?>.js"></script>
<script type="application/javascript">
    // Add any foundation modules you require to the end of this line.
    require(['utils/tween', 'jquery', 'jquery-ui', 'underscore', 'foundation', 'utils/foundation-dropdown', 'utils/placeholder', 'utils/aero', 'aero'], function() {
        $q(document).foundation();
        $q('input, textarea, select').placeholder();
    });
</script>
