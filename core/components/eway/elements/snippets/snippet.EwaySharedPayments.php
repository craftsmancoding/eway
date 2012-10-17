<?php
/**
 * EwaySharedPayments
 *
 * This Snippet integrates the Eway Shared Payments API so that you can securely funnel
 * payments to Eway's site.  This is sorta like PayPal for our friends in Australia,
 * New Zealand, and the UK. You must have an Eway account if you want to perform
 * real transactions!  See http://www.eway.com.au/
 *
 * LICENSE:
 * See the core/components/eway/docs/license.txt for full licensing info.
 *
 *
 * SNIPPET PARAMETERS:
 *
 * &returnID integer (optional) the pageID to redirect after the transaction is completed.
 *   Default is the current page.
 *
 * &cancelID integer (optional) the pageID to redirect to if the transaction is cancelled
 *  Default is the current page.
 *
 * &css string (optional): a URL (relative or absolute) referencing a CSS file that should be loaded.
 *
 * &test boolean (optional): if set, the following parameters will automatically be set:
 *  CustomerID: set to test account 87654321
 *  Amount = '10.00'
 *  Currency = 'AUD'
 *  returnID = the id of THIS page
 *  cancelID = the id of THIS page
 *
 * &CustomerID string usually set via a System Setting, but you can also set it in-line.
 *
 * &successTpl string name of Chunk to use to format a success message. All parameters are 
 * 		available as placeholders, as well as a [[+msg]] placeholder.
 *
 * &errorTpl string name of Chunk to use to format an error message. [[+msg]] will contain
 *		the error message.
 *
 * &successHook string name of a Snippet to execute after successful submission of the form.
 *
 * Also, any of the following parameters can be passed either via the $_POST'ed form fields
 * or by setting them explicitly in the Snippet call:
 * CustomerID, UserName, Amount, Currency, PageTitle, PageDescription, PageFooter, Language,
 * CompanyName, CustomerFirstName, CustomerLastName, CustomerAddress, CustomerCity,
 *  CustomerState, CustomerPostCode, CustomerCountry, CustomerEmail, CustomerPhone,
 * InvoiceDescription, CompanyLogo, PageBanner, MerchantReference, MerchantInvoice,
 * MerchantOption1, MerchantOption2, MerchantOption3, ModifiableCustomerDetails
 *
 * Values set in the Snippet call will override any values set in the $_POST.
 * The process is more tamper-proof when you set values in the Snippet call.
 * All sumitted parameters are stored in the $_SESSION array (see print_r($_SESSION['eway'])
 * for use in the 
 *
 * USAGE:
 *
 * Place the snippet call somewhere above your form -- you can use the [[$eway_sample_form]] as a sample
 * to get you started.  The form should submit to THIS page and use the "post" method.
 *
 * Always call this Snippet uncached!
 *
 * [[!EwaySharedPayments]]
 *
 * OR
 *
 * [[!EwaySharedPayments? &returnUrl=`12` &cancelUrl=`34`]]
 *
 * OR
 *
 * [[!EwaySharedPayments? &returnUrl=`12` &cancelUrl=`34` &css=`[[++assets_url]]css/my.css`]]
 *
 * OR
 *
 * You can also override any value that's not posted:
 *
 * [[!EwaySharedPayments? &CustomerPhone=`001 123 4567` &Language=`DE`]]
 *
 *
 * @var modX $modx
 * @var array $scriptProperties
 *
 * @name Eway
 * @url http://craftsmancoding.com/
 * @author Everett Griffiths <everett@craftsmancoding.com>
 * @package eway
 */


// Basic testing... bail if we don't have cUrl installed.
if (!function_exists('curl_exec')) {
	$props = array(
		'title' => $modx->lexicon('error'),
		'msg' => $modx->lexicon('eway.missing_curl')
	);
	return $modx->getChunk($errorTpl, $props);
}

$modx->getService('lexicon', 'modLexicon');
$modx->lexicon->load('eway:default');

require_once MODX_CORE_PATH.'components/eway/includes/Eway.php';

if (isset($test)) {
	$CustomerID = 87654321; // testing account.
	$scriptProperties['UserName'] = 'TestAccount';
	$scriptProperties['Amount'] = '10.00';
	$scriptProperties['Currency'] = 'AUD';
	$returnID = $modx->resource->get('id');
	$cancelID = $modx->resource->get('id');
}

// this allows for the CustomerID to be set via a Snippet parameter
if (!isset($CustomerID)) {
	$CustomerID = $modx->getOption('eway.customerID');
}

if (!isset($successTpl)) {
	$successTpl = 'eway_success';
}
if (!isset($errorTpl)) {
	$errorTpl = 'eway_error';
}

if (empty($CustomerID)) {
	$props = array(
		'title' => $modx->lexicon('error'),
		'msg' => $modx->lexicon('eway.missing_customerID')
	);
	return $modx->getChunk($errorTpl, $props);
}

if (isset($css) || !empty($css)) {
	$modx->regClientCSS($css);
}

if (!isset($returnID) || empty($returnID)) {
	$ReturnUrl = $modx->makeUrl($modx->resource->get('id'), '', '', 'full');
}
else {
	$ReturnUrl = $modx->makeUrl($returnID, '', '', 'full');
}

if (!isset($cancelID) || empty($cancelID)) {
	$CancelUrl = $modx->makeUrl($modx->resource->get('id'), '', '', 'full');
}
else {
	$CancelUrl = $modx->makeUrl($cancelID, '', '', 'full');
}

$modx->log(xPDO::LOG_LEVEL_DEBUG, 'EwaySharedPayments Snippet called with the following arguments: ' .print_r($scriptProperties, true));

// Process Payment
if (!empty($_POST)) {
	// If the current page containing the EwaySharedPayments is acting as the returnUrl,
	// we handle Eway's post-back and 'AccessPaymentCode'
	if (isset($_POST['AccessPaymentCode'])) {

		// some data sanitization.
		$AccessPaymentCode = preg_replace('/[^A-Za-z0-9]/', '*', $_POST['AccessPaymentCode']);
		$modx->log(xPDO::LOG_LEVEL_DEBUG, 'EwaySharedPayments post-back with AccessPaymentCode '.$AccessPaymentCode);
		$props = $_SESSION['eway'];
		$props['title'] = $modx->lexicon('success');
		$props['msg'] = $modx->lexicon('eway.success', array('AccessPaymentCode'=> $AccessPaymentCode));

		$keys = array_keys($props);
		$props['help'] = implode(',',$keys);
		// Hook
		if (isset($successHook) && !empty($successHook)) {
			$snippet_list = explode(',',$successHook);
			foreach ($snippet_list as $s) {
				$modx->runSnippet($s, $props);
			}
			

		}
		return $modx->getChunk($successTpl, $props);

	}
	// Otherwise, it's a regular form submission.
	$ewayurl.='?CustomerID='.$CustomerID;
	$ewayurl.="&UserName=".Eway::get($scriptProperties, $_POST, 'UserName');
	$ewayurl.="&Amount=".Eway::get($scriptProperties, $_POST, 'Amount');
	$ewayurl.="&Currency=".Eway::get($scriptProperties, $_POST, 'Currency');
	$ewayurl.="&PageTitle=".Eway::get($scriptProperties, $_POST, 'PageTitle');
	$ewayurl.="&PageDescription=".Eway::get($scriptProperties, $_POST, 'PageDescription');
	$ewayurl.="&PageFooter=".Eway::get($scriptProperties, $_POST, 'PageFooter');
	$ewayurl.="&Language=".Eway::get($scriptProperties, $_POST, 'Language');
	$ewayurl.="&CompanyName=".Eway::get($scriptProperties, $_POST, 'CompanyName');
	$ewayurl.="&CustomerFirstName=".Eway::get($scriptProperties, $_POST, 'CustomerFirstName');
	$ewayurl.="&CustomerLastName=".Eway::get($scriptProperties, $_POST, 'CustomerLastName');
	$ewayurl.="&CustomerAddress=".Eway::get($scriptProperties, $_POST, 'CustomerAddress');
	$ewayurl.="&CustomerCity=".Eway::get($scriptProperties, $_POST, 'CustomerCity');
	$ewayurl.="&CustomerState=".Eway::get($scriptProperties, $_POST, 'CustomerState');
	$ewayurl.="&CustomerPostCode=".Eway::get($scriptProperties, $_POST, 'CustomerPostCode');
	$ewayurl.="&CustomerCountry=".Eway::get($scriptProperties, $_POST, 'CustomerCountry');
	$ewayurl.="&CustomerEmail=".Eway::get($scriptProperties, $_POST, 'CustomerEmail');
	$ewayurl.="&CustomerPhone=".Eway::get($scriptProperties, $_POST, 'CustomerPhone');
	$ewayurl.="&InvoiceDescription=".Eway::get($scriptProperties, $_POST, 'InvoiceDescription');
	$ewayurl.="&CancelURL=".$CancelUrl;
	$ewayurl.="&ReturnUrl=".$ReturnUrl;
	$ewayurl.="&CompanyLogo=".Eway::get($scriptProperties, $_POST, 'CompanyLogo');
	$ewayurl.="&PageBanner=".Eway::get($scriptProperties, $_POST, 'PageBanner');
	$ewayurl.="&MerchantReference=".Eway::get($scriptProperties, $_POST, 'RefNum');
	$ewayurl.="&MerchantInvoice=".Eway::get($scriptProperties, $_POST, 'MerchantInvoice');
	$ewayurl.="&MerchantOption1=".Eway::get($scriptProperties, $_POST, 'MerchantOption1');
	$ewayurl.="&MerchantOption2=".Eway::get($scriptProperties, $_POST, 'MerchantOption2');
	$ewayurl.="&MerchantOption3=".Eway::get($scriptProperties, $_POST, 'MerchantOption3');
	$ewayurl.="&ModifiableCustomerDetails=".Eway::get($scriptProperties, $_POST, 'ModDetails');

	// Store to Session for later use
	$save_me = array_merge($_POST,$scriptProperties);
	$save_me['CancelURL'] = $CancelUrl;
	$save_me['ReturnUrl'] = $ReturnUrl;
	$save_me['CustomerID'] = $CustomerID;
    $save_me['posted_data'] = $_POST;
	$_SESSION['eway'] = $save_me;
        
	
	$spacereplace = str_replace(" ", "%20", $ewayurl);
	$posturl="https://au.ewaygateway.com/Request/$spacereplace";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $posturl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	if (CURL_PROXY_REQUIRED == 'True') {
		$proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
		curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
		curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		curl_setopt($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
	}

	$response = curl_exec($ch);

	$responsemode = Eway::fetch_data($response, '<result>', '</result>');
	$responseurl = Eway::fetch_data($response, '<uri>', '</uri>');

	// Redirect
	if ($responsemode == 'True') {
		$modx->log(xPDO::LOG_LEVEL_DEBUG, 'Forwarding to '.$responseurl);
		header("location: ".$responseurl);
		exit;
	}
	// Or Error
	else {
		$modx->log(xPDO::LOG_LEVEL_ERROR, "EwaySharedPayments Error! responsemode: $responsemode responseurl: $responseurl PostUrl: $posturl response: $response");
		$props = array(
			'title' => $modx->lexicon('error'),
			'msg' => $modx->lexicon('eway.error', array('error'=>Eway::fetch_data($response, '<Error>', '</Error>')))
		);

		return $modx->getChunk($errorTpl, $props);

	}

}

/*EOF*/