<?php

/* @var $this yii\web\View */

use yii\web\View;

$settings = Yii::$app->settings->getAll();
?>

<?php if ($settings['seo']['googleAnalytics']) : ?>
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

		ga('create', '<?= $settings['seo']['googleAnalytics'] ?>', 'auto');
		ga('send', 'pageview');
	</script>
<?php endif; ?>

<?php if ($settings['general']['reCaptchaSiteKey']) : ?>
	<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback" async defer></script>
	<script>
		var onloadCallback = function() {
			if (document.getElementById("captcha-response")) {
				grecaptcha.execute();
			}
		};

		function setResponse(response) {
			document.getElementById("captcha-response").value = response;
		}
	</script>
<?php endif; ?>

<?= Yii::$app->settings->get('footerContent', 'script') ?>

<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
