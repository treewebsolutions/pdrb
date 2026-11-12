<?php
/* @var $this yii\web\View */
/* @var $campaign \common\models\MarketingCampaign */
/* @var $recipient \common\models\MarketingRecipient */

use common\models\Page;
use tws\helpers\Url;
use yii\helpers\Html;

$socialNetwork = Yii::$app->settings->getCategory('socialNetwork');
$contact = Yii::$app->settings->getCategory('contact');
$address = implode(', ', array_filter([
	$contact['streetName'],
	$contact['streetNumber'],
	$contact['locality'],
	$contact['zipCode'],
	$contact['county'],
	$contact['country'] ? \common\models\Country::findAllCountries()[$contact['country']]->translation->name : null,
]));

$phones = [];
if ($contact['mobilePhone']) {
	$phones = array_filter(array_merge($phones, explode(',', $contact['mobilePhone'])));
}
if ($contact['fixedPhone']) {
	$phones = array_filter(array_merge($phones, explode(',', $contact['fixedPhone'])));
}

?>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td>

			<!-- Header Top Start -->
			<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
				<tr>
					<td>
						<table align="center" border="0" cellpadding="0" cellspacing="0" width="580" style="border-collapse: collapse;">
							<tr>
								<td align="left"  bgcolor="#303631">
									<!-- Space -->
									<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
										<tr><td style="font-size: 0; line-height: 0;" height="10">&nbsp;</td></tr>
									</table>
									<table align="center">
										<tr>
											<?php if ($address): ?>
												<td width="22">
													<img src="<?= implode('', array_filter([Url::base(true), '/img/mail/marker-icon-white.png'])) ?>" alt="location" />
												</td>
												<td style="color: #fff; font-size: 12px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">
													<?php if ($contact['latitude'] && $contact['longitude']): ?>
														<a style="color: #fff; font-size: 12px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif; text-decoration:none;" href="//www.google.com/maps/place/<?= $contact['latitude'] ?>,<?= $contact['longitude'] ?>"><?= $address ?></a>
													<?php else: ?>
														<?= $address ?>
													<?php endif; ?>
												</td>
											<?php endif; ?>
											<?php if ($phones[0]): ?>
												<td width="22"></td>
												<td width="22">
													<img src="<?= implode('', array_filter([Url::base(true), '/img/mail/phone-icon-white.png'])) ?>" alt="location" />
												</td>
												<td style="color: #fff; font-size: 12px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">
													<a style="color: #fff; font-size: 12px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif; text-decoration:none;" href="tel:<?= $phones[0] ?>">
														<?= $phones[0] ?>
													</a>
												</td>
											<?php endif; ?>
											<?php if ($contact['email']): ?>
												<td width="22"></td>
												<td width="22">
													<img src="<?= implode('', array_filter([Url::base(true), '/img/mail/mail-icon-white.png'])) ?>" alt="location" />
												</td>
												<td>
													<a style="color: #fff; font-size: 12px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif; text-decoration:none;" href="mailto:<?= $contact['email'] ?>">
														<?= $contact['email'] ?>
													</a>
												</td>
											<?php endif; ?>
										</tr>
									</table>
									<!-- Space -->
									<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
										<tr><td style="font-size: 0; line-height: 0;" height="10">&nbsp;</td></tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<!-- Header Top End -->

			<!-- Header Start -->
			<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
				<tr>
					<td style="padding:15px 0 0 0;">
						<table align="center" border="0" cellpadding="0" cellspacing="0" width="580" style="border-collapse: collapse;">
							<tr>
								<td>
									<table align="left" border="0" cellpadding="0" cellspacing="0" width="200" style="border-collapse: collapse;">
										<!-- logo -->
										<tr>
											<td align="left">
												<a href="<?= implode('', array_filter([Url::base(true), (Yii::$app->settings->get('defaultLanguage') == Yii::$app->language ? '' : '/' . mb_substr(Yii::$app->language, 0, 2)), '/'])) ?>">
													<img src="<?= implode('', array_filter([Url::base(true), '/img/logo.png'])) ?>" alt="<?= Yii::$app-name ?>" style="display: block; width: 150px;"/>
												</a>
											</td>
										</tr>
									</table>
									<table align="left" border="0" cellpadding="0" cellspacing="0" width="370" style="border-collapse: collapse;">
										<tr>
											<td  height="75" style="text-align: right; vertical-align: middle;">
												<a href="<?= implode('', array_filter([
													Url::base(true),
													(Yii::$app->settings->get('defaultLanguage') == Yii::$app->language ? '' : '/' . mb_substr(Yii::$app->language, 0, 2)),
													'/',
													Page::findPageByRoute(['site/about-us'])->translation->slug
												])) ?>" style="font-family:helvetica, Arial, sans-serif; color: #333333; font-size: 16px; text-decoration: none;"><?= Page::findPageByRoute(['site/about-us'])->translation->title ?></a> &nbsp;&nbsp;
												<a href="<?= implode('', array_filter([
													Url::base(true),
													(Yii::$app->settings->get('defaultLanguage') == Yii::$app->language ? '' : '/' . mb_substr(Yii::$app->language, 0, 2)),
													'/',
													Page::findPageByRoute(['article/default/index'])->translation->slug
												])) ?>" style="font-family:helvetica, Arial, sans-serif; color: #333333; font-size: 16px; text-decoration: none;"><?= Page::findPageByRoute(['article/default/index'])->translation->title ?></a> &nbsp;&nbsp;
												<a href="<?= implode('', array_filter([
													Url::base(true),
													(Yii::$app->settings->get('defaultLanguage') == Yii::$app->language ? '' : '/' . mb_substr(Yii::$app->language, 0, 2)),
													'/',
													Page::findPageByRoute(['site/contact'])->translation->slug
												])) ?>" style="font-family:helvetica, Arial, sans-serif; color: #333333; font-size: 16px; text-decoration: none;"><?= Page::findPageByRoute(['site/contact'])->translation->title ?></a>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<!-- Header End -->

			<!-- Banner Start -->
			<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
				<tr>
					<td>
						<table align="center" border="0" cellpadding="0" cellspacing="0" width="580" style="border-collapse: collapse;">
							<tr>
								<td>
									<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
										<tr>
											<td>
												<!-- Space -->
												<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
													<tr><td style="font-size: 0; line-height: 0;" height="20">&nbsp;</td></tr>
												</table>
												<table align="center" border="0" cellpadding="0" cellspacing="0" width="540" style="border-collapse: collapse;">
													<tr>
														<td>
															<table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
																<tr>
																	<td width="100%" align="left" style="font-size: 14px; line-height: 18px; font-family:helvetica, Arial, sans-serif; color:#343434;">
																		<?= strtr($campaign->translation->content, $recipient->getShortCodeValues()) ?>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
												<!-- Space -->
												<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
													<tr><td style="font-size: 0; line-height: 0;" height="30">&nbsp;</td></tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<!-- Banner End -->

			<!-- Footer Start -->
			<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
				<tr>
					<td>
						<table bgcolor="#fafafa" align="center" border="0" cellpadding="0" cellspacing="0" width="580" style="border-collapse: collapse;">
							<tr>
								<td>
									<!-- Space -->
									<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
										<tr><td style="font-size: 0; line-height: 0;" height="30">&nbsp;</td></tr>
									</table>
									<table align="center" border="0" cellpadding="0" cellspacing="0" width="540" style="border-collapse: collapse;">
										<tr>
											<td>
												<!-- First Column -->
												<table align="left" border="0" cellpadding="0" cellspacing="0" width="250" style="border-collapse: collapse;">
													<tr>
														<td>
															<a href="<?= implode('', array_filter([Url::base(true), (Yii::$app->settings->get('defaultLanguage') == Yii::$app->language ? '' : '/' . mb_substr(Yii::$app->language, 0, 2)), '/',])) ?>">
																<img src="<?= implode('', array_filter([Url::base(true), '/img/logo.png'])) ?>" alt="<?= Yii::$app-name ?>" style="display: block; width: 150px;"/>
															</a>
														</td>
													</tr>
													<!-- Space -->
													<tr><td style="font-size: 0; line-height: 0;" height="20">&nbsp;</td></tr>
													<tr>
														<td style="color: #999999; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">
															<?php if ($appDescription = Yii::$app->settings->get('appDescription', 'general')): ?>
																<?= $appDescription[Yii::$app->language] ?>
															<?php endif; ?>
														</td>
													</tr>
													<!-- Space -->
													<tr><td style="font-size: 0; line-height: 0;" height="15">&nbsp;</td></tr>
													<tr>
														<td>
															<?php if ($socialNetwork['facebookPage']): ?>
																<table align="left" border="0" cellpadding="0" cellspacing="0" width="45" style="border-collapse: collapse;">
																	<tr>
																		<td>
																			<a href="<?= $socialNetwork['facebookPage'] ?>">
																				<img src="<?= implode('', array_filter([Url::base(true), '/img/mail/facebook-icon.png'])) ?>"  alt="Facebook" style="display: block; width: 40px;" />
																			</a>
																		</td>
																	</tr>
																</table>
															<?php endif; ?>
															<?php if ($socialNetwork['twitterPage']): ?>
																<table align="left" border="0" cellpadding="0" cellspacing="0" width="45" style="border-collapse: collapse;">
																	<tr>
																		<td>
																			<a href="<?= $socialNetwork['twitterPage'] ?>">
																				<img src="<?= implode('', array_filter([Url::base(true), '/img/mail/twitter-icon.png'])) ?>"  alt="Twitter" style="display: block; width: 40px;" />
																			</a>
																		</td>
																	</tr>
																</table>
															<?php endif; ?>
															<?php if ($socialNetwork['linkedinPage']): ?>
																<table align="left" border="0" cellpadding="0" cellspacing="0" width="45" style="border-collapse: collapse;">
																	<tr>
																		<td>
																			<a href="<?= $socialNetwork['linkedinPage'] ?>">
																				<img src="<?= implode('', array_filter([Url::base(true), '/img/mail/linkedin-icon.png'])) ?>"  alt="Linked In" style="display: block; width: 40px;" />
																			</a>
																		</td>
																	</tr>
																</table>
															<?php endif; ?>
														</td>
													</tr>
												</table>
												<!-- Gutter 20px -->
												<table align="left" border="0" cellpadding="0" cellspacing="0" width="40" style="border-collapse: collapse;">
													<tr>
														<td>
															&nbsp;
														</td>
													</tr>
												</table>
												<!-- Second Column -->
												<table align="left" border="0" cellpadding="0" cellspacing="0" width="250" style="border-collapse: collapse;">
													<?php if ($address): ?>
														<!-- Space -->
														<tr><td style="font-size: 0; line-height: 0;" height="57">&nbsp;</td></tr>
														<tr>
															<td width="22">
																<img src="<?= implode('', array_filter([Url::base(true), '/img/mail/marker-icon.png'])) ?>" alt="<?= Yii::$app-name ?>" />
															</td>
															<td style="color: #999999; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">
																<?php if ($contact['latitude'] && $contact['longitude']): ?>
																	<a style="color: #24a828; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif; text-decoration:none;" href="//www.google.com/maps/place/<?= $contact['latitude'] ?>,<?= $contact['longitude'] ?>" target="_blank"><?= $address ?></a>
																<?php else: ?>
																	<?= $address ?>
																<?php endif; ?>
															</td>
														</tr>
													<?php endif; ?>
													<?php if (!empty($phones)): ?>
														<!-- Space -->
														<tr><td style="font-size: 0; line-height: 0;" height="10">&nbsp;</td></tr>
														<tr>
															<td width="22">
																<img src="<?= implode('', array_filter([Url::base(true), '/img/mail/phone-icon.png'])) ?>" alt="<?= Yii::$app->name ?>" />
															</td>
															<?php
																$numbers = [];
																foreach ($phones as $phone) {
																	$numbers[] = Html::a($phone, "tel:{$phone}", ['style' => 'color: #24a828; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif; text-decoration:none;']);
																}
															?>
															<td style="color: #999999; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">
																<?= implode(', ', array_filter($numbers)) ?>
															</td>
														</tr>
													<?php endif; ?>
													<?php if ($contact['email']): ?>
														<!-- Space -->
														<tr><td style="font-size: 0; line-height: 0;" height="10">&nbsp;</td></tr>
														<tr>
															<td width="22">
																<img src="<?= implode('', array_filter([Url::base(true), '/img/mail/mail-icon.png'])) ?>" alt="<?= Yii::$app->name ?>" />
															</td>
															<td>
																<a style="color: #24a828; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif; text-decoration:none;" href="mailto:<?= $contact['email'] ?>"><?= $contact['email'] ?></a>
															</td>
														</tr>
													<?php endif; ?>
												</table>
											</td>
										</tr>
									</table>
									<!-- Space -->
									<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
										<tr><td style="font-size: 0; line-height: 0;" height="30">&nbsp;</td></tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>

			</table>
			<!-- Footer End -->

			<!-- Subfooter Start -->
			<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
				<tr>
					<td>
						<table bgcolor="#f5f5f5" align="center" border="0" cellpadding="0" cellspacing="0" width="580" style="border-collapse: collapse;">
							<tr>
								<td>
									<!-- Space -->
									<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
										<tr><td style="font-size: 0; line-height: 0;" bgcolor="#eaeaea" height="1">&nbsp;</td></tr>
										<tr><td style="font-size: 0; line-height: 0;" height="20">&nbsp;</td></tr>
									</table>
									<table align="center" border="0" cellpadding="0" cellspacing="0" width="540" style="border-collapse: collapse;">
										<tr>
											<td align="center" style="color: #999999; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">
												<?= Yii::t('common', '&copy; {0} {1}. All rights reserved.', [Yii::$app->name, date('Y')]) ?>
												<a style="color: #24a828; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif; text-decoration:none;" href="//www.treewebsolutions.com" target="_blank" title="Tree Web Solutions"><?= Yii::t('common', 'Developed by {0}.', 'TWS') ?></a>
											</td>
										</tr>
										<tr>
											<td align="center" style="color: #999999; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">
												<a href="<?= implode('', array_filter([
													Url::base(true),
													(Yii::$app->settings->get('defaultLanguage') == Yii::$app->language ? '' : '/' . mb_substr(Yii::$app->language, 0, 2)),
													'/',
													Page::findPageByRoute(['site/unsuscribe'])->translation->slug,
													'?id=',
													Yii::$app->security->maskToken((string) $recipient->id)
												])) ?>" style="color: #24a828; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif; text-decoration:none;"><?= Yii::t('label', 'Unsuscribe') ?></a>
											</td>
										</tr>
									</table>
									<!-- Space -->
									<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
										<tr><td style="font-size: 0; line-height: 0;" height="20">&nbsp;</td></tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>

			</table>
			<!-- Subfooter End -->


		</td>
	</tr>
</table>