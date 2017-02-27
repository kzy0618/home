<?php
/**
 * ownCloud - home
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Shawn, Ahamad <syu702@aucklanduni.ac.nz>
 * @copyright Shawn, Ahamad 2016
 */

namespace OCA\Home\AppInfo;

use OCP\AppFramework\App;

$app = new App('home');
$container = $app->getContainer();

$container->query('OCP\INavigationManager')->add(function () use ($container) {
	$urlGenerator = $container->query('OCP\IURLGenerator');
	$l10n = $container->query('OCP\IL10N');
	return [
		// the string under which your app will be referenced in owncloud
		'id' => 'home',

		// sorting weight for the navigation. The higher the number, the higher
		// will it be listed in the navigation
		'order' => 0,

		// the route that will be shown on startup
		'href' => $urlGenerator->linkToRoute('home.page.index'),

		// the icon that will be shown in the navigation
		// this file needs to exist in img/
		'icon' => $urlGenerator->imagePath('home', 'app.svg'),

		// the title of your application. This will be used in the
		// navigation or on the settings page of your app
		'name' => $l10n->t('Home'),
	];
});
