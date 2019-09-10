<?php
/**
* Plugin Name: Easy Amazon Products
* Plugin URI: http://www.francescosganga.it/wordpress/plugins/easy-amazon-products/
* Description: Insert Amazon Products in your blog
* Version: 1.0.0
* Author: Francesco Sganga
* Author URI: http://www.francescosganga.it/
**/

function eap_assets() {
	wp_enqueue_style("eap", plugin_dir_url(__FILE__) . "assets/style.css");
}

add_action('wp_enqueue_scripts', 'eap_assets');

function eap_admin_assets() {
	
}

add_action("admin_enqueue_scripts", "eap_admin_assets");

function eap_init() {
	register_setting('eap-options', 'eap-amazon-country', array(
		'type' => 'string', 
		'sanitize_callback' => 'sanitize_text_field',
		'default' => "it"
	));

	register_setting('eap-options', 'eap-amazon-tag', array(
		'type' => 'string', 
		'sanitize_callback' => 'sanitize_text_field',
		'default' => ""
	));

	register_setting('eap-options', 'eap-amazon-api-access-key', array(
		'type' => 'string', 
		'sanitize_callback' => 'sanitize_text_field',
		'default' => ""
	));

	register_setting('eap-options', 'eap-amazon-api-secret', array(
		'type' => 'string', 
		'sanitize_callback' => 'sanitize_text_field',
		'default' => ""
	));
}
add_action('admin_init', 'eap_init');

function eap_options_panel(){
	add_menu_page('Easy Amazon Products', 'Easy Amazon Products', 'manage_options', 'eap-options', 'eap_options_settings');
	add_submenu_page('eap-options', 'About', 'About', 'manage_options', 'eap-option-about', 'eap_options_about');
}
add_action('admin_menu', 'eap_options_panel');

function eap_options_settings(){
	wp_enqueue_script("eap-admin", plugin_dir_url(__FILE__) . "assets/admin-main.js", array(), "1.0.0", true);
	?>
	<div class="wrap">
		<h1>Easy Amazon Products</h1>
		<h2>Settings Section</h2>
		<form method="post" action="options.php">
		<?php settings_fields('eap-options'); ?>
		<?php do_settings_sections('eap-options'); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Operating Country</th>
				<td>
					<select name="eap-amazon-country" data-current-value="<?php print get_option("eap-amazon-country"); ?>">
						<option value="com.au">Australia</option>
						<option value="com.br">Brazil</option>
						<option value="ca">Canada</option>
						<option value="cn">China</option>
						<option value="fr">France</option>
						<option value="de">Germany</option>
						<option value="in">India</option>
						<option value="it">Italy</option>
						<option value="co.jp">Japan</option>
						<option value="com.mx">Mexico</option>
						<option value="es">Spain</option>
						<option value="com.tr">Turkey</option>
						<option value="co.uk">United Kingdom</option>
						<option value="com">United States</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Amazon Tag</th>
				<td>
					<input type="text" name="eap-amazon-tag" value="<?php echo get_option('eap-amazon-tag'); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Amazon API</th>
				<td>
					<input type="text" name="eap-amazon-api-access-key" value="<?php echo get_option('eap-amazon-api-access-key'); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Amazon API Key</th>
				<td>
					<input type="text" name="eap-amazon-api-secret" value="<?php echo get_option('eap-amazon-api-secret'); ?>" />
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
		</form>
		<hr />
		<h2>How to use</h2>
		<h3>Shortcode</h3>
		<p>
			[eap]<br />
			<br />
			<strong>Shortcode Attributes</strong><br />
			<strong>asin</strong>: ASIN is Amazon unique identifier for products. You can insert multiple ASINs separating them by comma (ASIN1,ASIN2)<br />
			<strong>width</strong>: Box's Width, valid only for multiple ASINs.<br />
			<br />
		</p>
		<h2>Examples</h2>
		<p>
			[eap asin="B07PB8TYCJ"]<br />
			<img src="<?php print plugin_dir_url(__FILE__) . "assets/example01.png"; ?>" />
			<hr />
			[eap asin="B07PB8TYCJ,B07TGT88Z9,B07MQDZ8B8"]<br />
			<img src="<?php print plugin_dir_url(__FILE__) . "assets/example02.png"; ?>" />
		</p>
	</div>
	<?php
}

function eap_options_about(){
	?>
	<h1>About</h1>
	<h2>Under Construction</h2>
	<?php
}

//shortcode sections

function eap_shortcode($atts) {
	if(!isset($atts['asin']))
		return "ASIN field is mandatory.";

	if(!isset($atts['width']))
		$atts['width'] = "50";

	$output = '';

	$products = explode(",", $atts['asin']);

	if(count($products) == 1)
		$single = true;
	else
		$single = false;

	if($single)
		$template = eap_get_single_template();
	else
		$template = eap_get_multi_template();

	if(count($products) == 1)
		$atts['width'] = "100";

	$output .= "<div class=\"eap-row\">";
	foreach($products as $product) {
		$asin = $product;
		$product = eap_api_make_product_request($product);
		if(!$product)
			break;

		if(!$single)
			$product['item_title'] = substr($product['item_title'], 0, 20) . '...';
		
		if($product['item_newprice'] != $product['item_price'])
			$product['item_price'] = "<span class=\"oldprice\">{$product['item_price']}</span>{$product['item_newprice']}";
		else
			$product['item_price'] = "{$product['item_price']}";

		$currentTemplate = $template;
		$currentTemplate = str_replace("{box_width}", $atts['width'], $currentTemplate);
		$currentTemplate = str_replace("{item_title}", $product['item_title'], $currentTemplate);
		$currentTemplate = str_replace("{item_price}", $product['item_price'], $currentTemplate);
		$currentTemplate = str_replace("{item_image}", $product['item_image'], $currentTemplate);
		$currentTemplate = str_replace("{item_asin}", $asin, $currentTemplate);
		$currentTemplate = str_replace("{amazon_country}", get_option("eap-amazon-country"), $currentTemplate);
		$currentTemplate = str_replace("{amazon_tag}", get_option("eap-amazon-tag"), $currentTemplate);
		$currentTemplate = str_replace("{amazon_api_access_key}", get_option("eap-amazon-api-access-key"), $currentTemplate);

		$output .= $currentTemplate;
	}
	$output .= "</div>";

	sleep(0.5);

	return $output;
}
add_shortcode("eap", "eap_shortcode");

//templates
function eap_get_multi_template() {
	$file = plugin_dir_path(__FILE__) . "templates/multi.tpl";
	$h = fopen($file, "r");
	$template = fread($h, filesize($file));
	fclose($h);

	return $template;
}

function eap_get_single_template() {
	$file = plugin_dir_path(__FILE__) . "templates/single.tpl";
	$h = fopen($file, "r");
	$template = fread($h, filesize($file));
	fclose($h);

	return $template;
}


//amazon Product Advertising API functions
function eap_api_make_product_request($asin) {
	$method = 'GET';
	$host = 'webservices.amazon.' . get_option("eap-amazon-country");
	$uri = '/onca/xml';

	$params = Array();
	$params['Operation'] = 'ItemLookup';
	$params['ItemId'] = $asin;
	$params['IncludeReviewsSummary'] = false;
	$params['ResponseGroup'] = 'Medium,OfferSummary';
	$params['Service'] = 'AWSECommerceService';
	$params['AssociateTag'] = get_option("eap-amazon-tag");
	$params['AWSAccessKeyId'] = get_option("eap-amazon-api-access-key");
	$params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
	$params['Version'] = '2011-08-01';

	ksort($params);

	$canonicalizedQuery = Array();
	foreach($params as $param => $value) {
		$param = str_replace('%7E', '~', rawurlencode($param));
		$value = str_replace('%7E', '~', rawurlencode($value));
		$canonicalizedQuery[] = $param . '=' . $value;
	}
	$canonicalizedQuery = implode('&', $canonicalizedQuery);

	$stringToSign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalizedQuery;
	$signature = eap_api_generate_signature($stringToSign);

	$request = 'https://' . $host . $uri . '?' . $canonicalizedQuery . '&Signature=' . $signature;

	$result = eap_curl_get_content($request);
	if(!$result)
		return false;

	$result = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
	$result = json_decode(json_encode((array)$result), true);
	$result = $result['Items']['Item'];
	return Array(
		'item_title' => $result['ItemAttributes']['Title'],
		'item_image' => eap_imageurl_to_base64($result['LargeImage']['URL']),
		'item_manufacturer' => $result['ItemAttributes']['Manufacturer'],
		'item_price' => str_replace("EUR", "&euro;", $result['ItemAttributes']['ListPrice']['FormattedPrice']),
		'item_newprice' => str_replace("EUR", "&euro;", $result['OfferSummary']['LowestNewPrice']['FormattedPrice']),
		'item_features' => $result['ItemAttributes']['Feature'],
		'item_description' => $result['EditorialReviews']['EditorialReview']['Content']
	);
}

function eap_api_generate_signature($stringToSign) {
	$signature = base64_encode(hash_hmac("sha256", $stringToSign, get_option("eap-amazon-api-secret"), true));
	$signature = str_replace('%7E', '~', rawurlencode($signature));

	return $signature;
}

function eap_curl_get_content($url){
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	$html = curl_exec($curl);
	curl_close($curl);

	if(empty($html))
		return false;
	
	return $html;
}

function eap_imageurl_to_base64($url) {
	$image = file_get_contents($url);
	return "data:image/jpg;base64," . base64_encode($image);
}