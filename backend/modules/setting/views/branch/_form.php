<?php
/* @var $this yii\web\View */
/* @var $model backend\modules\setting\models\BranchForm */
/* @var $form backend\widgets\ActiveForm */

use backend\widgets\ActiveForm;
use common\models\Branch;
use common\models\Country;
use kartik\select2\Select2;
use kartik\touchspin\TouchSpin;
use yii\bootstrap\Tabs;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin([
	'id' => mb_strtolower($model->formName()),
	'options' => [
		'novalidate' => true,
	],
	'validateOnType' => true,
]); ?>
<div class="form-body">
	<div class="form-fields">
		<?php if ($model->hasErrors()): ?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<div class="alert-table">
					<div class="row">
						<div class="icon">
							<span class="glyphicon glyphicon-remove-sign"></span>
						</div>
						<?= $form->errorSummary($model, ['header' => '']) ?>
						<div class="close">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="panel blue-hoki">
			<div class="panel-title">
				<div class="panel-heading"><?= Yii::t('common', 'General') ?></div>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-6">
						<?= $form->field($model, 'name')->textInput() ?>
					</div>
					<div class="col-sm-3">
						<?= $form->field($model, 'status')->widget(Select2::class, [
							'data' => ArrayHelper::getColumn(Branch::getStatusLabels(), 'label'),
							'pluginLoading' => false,
							'pluginOptions' => [
								'allowClear' => false,
								'placeholder' => Yii::t('common', 'Choose'),
							],
						]) ?>
					</div>
					<div class="col-sm-3">
						<?= $form->field($model, 'sort_order')->widget(TouchSpin::class, [
							'pluginOptions' => [
								'min' => 1,
								'max' => PHP_INT_MAX,
								'step' => 1,
								'decimals' => 0,
								'boostat' => 5,
								'maxboostedstep' => 10,
								'verticalbuttons' => true,
							],
						]) ?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4">
						<?= $form->field($model, 'mobile_phone')->input('tel') ?>
					</div>
					<div class="col-sm-4">
						<?= $form->field($model, 'fixed_phone')->input('tel') ?>
					</div>
					<div class="col-sm-4">
						<?= $form->field($model, 'fax')->input('tel') ?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<?= $form->field($model, 'email')->input('email') ?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4">
						<?= $form->field($model, 'last_name')->textInput() ?>
					</div>
					<div class="col-sm-4">
						<?= $form->field($model, 'first_name')->textInput() ?>
					</div>
					<div class="col-sm-4">
						<?= $form->field($model, 'middle_name')->textInput() ?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4">
						<?= $form->field($model, 'company')->textInput() ?>
					</div>
					<div class="col-sm-4">
						<?= $form->field($model, 'tin')->textInput() ?>
					</div>
					<div class="col-sm-4">
						<?= $form->field($model, 'registration_number')->textInput() ?>
					</div>
				</div>
				<?php
				$i18nFields = [];
				foreach (\common\models\Language::findAllLanguages() as $language) {
					$i18nFields[] = [
						'label' => mb_strtoupper($language->language),
						'content' => $form->field($model, "scheduleI18n[{$language->language_id}]")->textarea(['rows' => 3]),
						'active' => $language->language_id === Yii::$app->language,
					];
				}
				?>
				<?= Tabs::widget([
					'items' => $i18nFields,
				]) ?>
			</div>
		</div>

		<div class="panel blue-hoki">
			<div class="panel-title">
				<div class="panel-heading"><?= Yii::t('label', 'Address') ?></div>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="text-info-icon fa-info-circle text-muted margin-bottom-10"><?= Yii::t('backend', 'Drag the map marker to the desired position.') ?></div>
						<div id="map-container" style="width: 100%; height: 400px;"></div>
					</div>
					<div class="col-sm-6">
						<div class="row">
							<div class="col-sm-12">
								<?= $form->field($model, 'address')->textInput() ?>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<?= $form->field($model, 'latitude')->textInput() ?>
							</div>
							<div class="col-sm-6">
								<?= $form->field($model, 'longitude')->textInput() ?>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<?= $form->field($model, 'street_name')->textInput() ?>
							</div>
							<div class="col-sm-6">
								<?= $form->field($model, 'street_number')->textInput() ?>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<?= $form->field($model, 'locality')->textInput() ?>
							</div>
							<div class="col-sm-6">
								<?= $form->field($model, 'zip_code')->textInput() ?>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<?= $form->field($model, 'county')->textInput() ?>
							</div>
							<div class="col-sm-6">
								<?= $form->field($model, 'country')->widget(Select2::class, [
									'data' => ArrayHelper::map(Country::findAllCountries(), 'iso_alpha2', 'translation.name'),
									'pluginLoading' => false,
									'pluginOptions' => [
										'allowClear' => true,
										'placeholder' => Yii::t('common', 'Choose'),
									],
								]) ?>
							</div>
						</div>
						<?= Html::checkbox('', true, [
							'id' => 'update-address-checkbox',
							'value' => 1,
							'label' => Yii::t('backend', 'Update address while dragging the map marker'),
							'labelOptions' => [
								'class' => 'mt-checkbox mt-checkbox-outline',
							],
						]) ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="form-actions floating">
		<?= Html::submitButton('<span class="fa fa-check"></span>', [
			'class' => 'btn btn-xlg btn-fab btn-success',
			'title' => Yii::t('common', 'Save'),
			'data' => [
				'toggle' => 'tooltip',
			],
		]) ?>
	</div>
</div>
<?php ActiveForm::end(); ?>

<script type="text/javascript">
	(function (window, document) {
		var defaultLat = <?= json_encode($model->latitude ?: (Yii::$app->settings->get('latitude') ?: 45.7519379)) ?>,
			defaultLng = <?= json_encode($model->longitude ?: (Yii::$app->settings->get('longitude') ?: 21.2259002)) ?>,
			controls = {
				latitude: document.getElementById(<?= json_encode(Html::getInputId($model, 'latitude')) ?>),
				longitude: document.getElementById(<?= json_encode(Html::getInputId($model, 'longitude')) ?>),
				address: document.getElementById(<?= json_encode(Html::getInputId($model, 'address')) ?>),
				locality: document.getElementById(<?= json_encode(Html::getInputId($model, 'locality')) ?>),
				county: document.getElementById(<?= json_encode(Html::getInputId($model, 'county')) ?>),
				country: document.getElementById(<?= json_encode(Html::getInputId($model, 'country')) ?>),
				zipCode: document.getElementById(<?= json_encode(Html::getInputId($model, 'zip_code')) ?>),
				streetName: document.getElementById(<?= json_encode(Html::getInputId($model, 'street_name')) ?>),
				streetNumber: document.getElementById(<?= json_encode(Html::getInputId($model, 'street_number')) ?>)
			},
			updateAddressCheckbox = document.getElementById('update-address-checkbox'),
			map, marker, geocodeTimeout;

		var fillAddress = function (components, keepAddress) {
			var parts = {locality: '', county: '', country: '', zipCode: '', streetName: '', streetNumber: ''},
				typeMap = {
					locality: ['locality', 'long_name'],
					administrative_area_level_1: ['county', 'long_name'],
					country: ['country', 'short_name'],
					postal_code: ['zipCode', 'long_name'],
					route: ['streetName', 'long_name'],
					street_number: ['streetNumber', 'long_name']
				};

			for (var i = 0; i < components.length; i++) {
				var mapping = typeMap[components[i].types[0]];
				if (mapping) {
					parts[mapping[0]] = components[i][mapping[1]].trim();
				}
			}
			for (var key in parts) {
				controls[key].value = parts[key];
			}
			if (!keepAddress) {
				controls.address.value = parts.streetName + (parts.streetName && parts.streetNumber ? ', ' + parts.streetNumber : '');
			}
			if (controls.country.className.indexOf('select2-hidden-accessible') !== -1) {
				controls.country.dispatchEvent(new Event('change'));
			}
		};

		var setPosition = function (latLng) {
			marker.setPosition(latLng);
			map.setCenter(latLng);
			controls.latitude.value = latLng.lat().toFixed(8);
			controls.longitude.value = latLng.lng().toFixed(8);
		};

		var geocode = function (address) {
			if (!address) {
				return;
			}
			new google.maps.Geocoder().geocode({address: address}, function (results, status) {
				if (status === google.maps.GeocoderStatus.OK && results.length) {
					fillAddress(results[0].address_components || [], true);
					setPosition(results[0].geometry.location);
				}
			});
		};

		var reverseGeocode = function (latLng) {
			new google.maps.Geocoder().geocode({location: latLng}, function (results, status) {
				if (status === google.maps.GeocoderStatus.OK && results.length) {
					fillAddress(results[0].address_components || [], false);
				}
			});
		};

		window.initMap = function () {
			var position = new google.maps.LatLng(defaultLat, defaultLng);

			map = new google.maps.Map(document.getElementById('map-container'), {
				zoom: 14,
				center: position,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			});
			marker = new google.maps.Marker({position: position, draggable: true, map: map});

			google.maps.event.addListener(marker, 'dragend', function (e) {
				if (updateAddressCheckbox.checked) {
					reverseGeocode(e.latLng);
				}
				controls.latitude.value = e.latLng.lat().toFixed(8);
				controls.longitude.value = e.latLng.lng().toFixed(8);
			});

			controls.address.addEventListener('change', function (e) {
				clearTimeout(geocodeTimeout);
				geocode(e.target.value);
			});
			controls.address.addEventListener('keyup', function (e) {
				clearTimeout(geocodeTimeout);
				geocodeTimeout = setTimeout(function () {
					geocode(e.target.value);
				}, 600);
			});
		};
	})(window, document);
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= Html::encode(Yii::$app->settings->get('googleMapKey')) ?>&language=<?= Html::encode(Yii::$app->language) ?>&loading=async&callback=initMap"></script>
