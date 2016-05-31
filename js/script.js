/**
 * ownCloud - home
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Shawn <syu702@aucklanduni.ac.nz>, Ahmad <aalk942@auckland.ac.nz>
 * @copyright Shawn, Ahmad 2016
 */

(function ($, OC) {

	$(document).ready(function () {
		var anchor = $('#logout').find('img').clone();
		$('#logout').text('Log in').prepend(anchor);

		$('#login').click(function () {
			var url = OC.generateUrl('/login');
			//$.get(url);
                        alert(url);
			window.location.href=url;
		});

		var map = L.map('map').setView([-36.853904, 174.767240], 13);
		var citiesLayer = L.geoJson('cities').addTo('map');
		map.fitBounds(citiesLayer.getBounds());
	});

})(jQuery, OC);
