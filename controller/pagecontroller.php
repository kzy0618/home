<?php
/**
 * ownCloud - home
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Shawn, Ahmad <syu702@aucklanduni.ac.nz>
 * @copyright Shawn, Ahmad 2016
 */

namespace OCA\Home\Controller;

use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

class PageController extends Controller {


	private $userId;

	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
         * @PublicPage
	 */
	public function index() {
		$params = ['user' => $this->userId];
        $csp = new ContentSecurityPolicy();
        // Allows to access resources from a specific domain. Use * to allow everything from all domains.
        // here we allow ALL Javascript, images, styles, and fonts from ALL domains. Otherwise, Google Map will not be properly rendered.
        $csp->addAllowedScriptDomain("*")->addAllowedImageDomain("*")->addAllowedStyleDomain("*")->addAllowedFontDomain("*");
		$response = new TemplateResponse('home', 'main', $params);  // templates/main.php
        $response->setContentSecurityPolicy($csp);
        return $response;
	}
}
