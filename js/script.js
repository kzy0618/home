/**
 * ownCloud - home
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Shawn, Ahamad <syu702@aucklanduni.ac.nz>
 * @copyright Shawn, Ahamad 2016
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
	});

})(jQuery, OC);
