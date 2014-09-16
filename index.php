<?php
	/** 
	Sample food truck data:
	array(22) { 
		["location"]=> array(3) { 
			["needs_recoding"]=> bool(false) 
			["longitude"]=> string(17) "-122.401160370289" 
			["latitude"]=> string(15) "37.790838433222" 
		} 
		["status"]=> string(8) "APPROVED" 
		["expirationdate"]=> string(19) "2015-03-15T00:00:00" 
		["permit"]=> string(10) "14MFF-0021" ["block"]=> string(4) "0289" 
		["received"]=> string(19) "Mar 11 2014 2:00PM" 
		["facilitytype"]=> string(5) "Truck" 
		["blocklot"]=> string(7) "0289001" 
		["locationdescription"]=> string(47) "BUSH ST: TREASURY PL to PETRARCH PL (220 - 251)" 
		["cnn"]=> string(7) "3422000" 
		["priorpermit"]=> string(1) "1" 
		["approved"]=> string(19) "2014-03-11T14:12:44" 
		["schedule"]=> string(159) "http://bsm.sfdpw.org/PermitsTracker/reports/report.aspx?title=schedule&report=rptSchedule&params=permit=14MFF-0021&ExportPDF=1&Filename=14MFF-0021_schedule.pdf" 
		["address"]=> string(11) "225 BUSH ST" 
		["applicant"]=> string(12) "Curry Up Now" 
		["lot"]=> string(3) "001" 
		["fooditems"]=> string(79) "Chicken Tiki Masala Burritos: Paneer Tiki Masala Burritos: Samosas: Mango Lassi" 
		["longitude"]=> string(17) "-122.401160370299" 
		["latitude"]=> string(16) "37.7908384194517" 
		["objectid"]=> string(6) "525602" 
		["y"]=> string(11) "2116003.915" 
		["x"]=> string(10) "6012345.54" 
	}
	
	Yelp data:
	array(18) { 
		["is_claimed"]=> bool(true) 
		["rating"]=> float(4) 
		["mobile_url"]=> string(50) "http://m.yelp.com/biz/curry-up-now-san-francisco-6" 
		["rating_img_url"]=> string(86) "http://s3-media4.fl.yelpcdn.com/assets/2/www/img/c2f3dd9799a5/ico/stars/v1/stars_4.png" 
		["review_count"]=> int(750) 
		["name"]=> string(12) "Curry Up Now" 
		["snippet_image_url"]=> string(67) "http://s3-media4.fl.yelpcdn.com/photo/ncRVI3bML8OqvdZtoAeCNQ/ms.jpg" 
		["rating_img_url_small"]=> string(92) "http://s3-media4.fl.yelpcdn.com/assets/2/www/img/f62a5be2f902/ico/stars/v1/stars_small_4.png" 
		["url"]=> string(52) "http://www.yelp.com/biz/curry-up-now-san-francisco-6" 
		["phone"]=> string(10) "4157353667" 
		["snippet_text"]=> string(155) "so glad i work close to one of their trucks! i love the paneer burrito, so that's pretty much all i ever get, though i did try the deconstructed samosa..." 
		["image_url"]=> string(68) "http://s3-media3.fl.yelpcdn.com/bphoto/PIYg5NTkZ4tQW7ioN2ELJw/ms.jpg" 
		["categories"]=> array(2) { 
			[0]=> array(2) { 
				[0]=> string(11) "Food Trucks" 
				[1]=> string(10) "foodtrucks" 
			} 
			[1]=> array(2) { 
				[0]=> string(6) "Indian" 
				[1]=> string(6) "indpak" 
			} 
		} 
		["display_phone"]=> string(15) "+1-415-735-3667" 
		["rating_img_url_large"]=> string(92) "http://s3-media2.fl.yelpcdn.com/assets/2/www/img/ccf2b76faa2c/ico/stars/v1/stars_large_4.png" 
		["id"]=> string(28) "curry-up-now-san-francisco-6" 
		["is_closed"]=> bool(false) 
	}
	*/

	require_once('oauth.php');
	
	function fetch_data($uri) {
		$ch = curl_init($uri);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		curl_close($ch);
		return json_decode($response, true);
	}
	
	// Fetch SF food truck data.
	function get_food_trucks() {
		return fetch_data('http://data.sfgov.org/resource/rqzj-sfat.json');
	}
	
	// Use Yelp data to guess food truck hours.
	function get_yelp_data_for_truck($vendor_name, $lat, $long) {
		// Configuration.
		$consumer_key = 'AWlPaJb3wIoEQsjSttw23Q';
		$consumer_secret = 'kFbLDMqxFMYnz0E3qc8tDKnwkFk';
		$token = 'KwYAlz6BUP09hK7jCMQMVaVdbfiBOJpF';
		$token_secret = '5OugPyEKrsrktFj280l_JzB-5N4';
		
		// Search params.
		$params = array(
			'term' => $vendor_name,
			'category_filter' => 'foodtrucks,foodstands',
			'location' => 'San Francisco, CA',
			'cll' => (string)$lat.",".(string)$long,
			'limit' => 1, // For simiplicity's sake, get best match only.
		);
		
		// Build the request.
		$unsigned_uri = "http://api.yelp.com/v2/search/?" . http_build_query($params);
		// Token object built using the OAuth library
		$token = new OAuthToken($token, $token_secret);
		// Consumer object built using the OAuth library
		$consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		// Yelp uses HMAC SHA1 encoding
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		$oauthrequest = OAuthRequest::from_consumer_and_token(
			$consumer, 
			$token, 
			'GET', 
			$unsigned_uri
		);
		// Sign the request
		$oauthrequest->sign_request($signature_method, $consumer, $token);
		// Get the signed URL
		$signed_url = $oauthrequest->to_url();
		
		$results = fetch_data($signed_url);
		// Ensure a business listing is returned and the location is not closed permanently.
		if (array_key_exists("businesses", $results) && 
			!$results["businesses"][0]["is_closed"]) {
			return $results["businesses"][0];
		}
		return null;
	}
	
	function get_food_truck_data() {
		$food_trucks_src = get_food_trucks();
		// Test data.
		/* $food_trucks_src = array(array(
			"applicant" => "Curry Up Now",
			"latitude" => "37.790838433222",
			"longitude" =>  "-122.401160370289",
			"fooditems" => "Chicken Tiki Masala Burritos: Paneer Tiki Masala Burritos: Samosas: Mango Lassi",
			"status" => "APPROVED",
		)); */
		// Only keep food truck data we care about.
		$food_trucks = array();
		foreach ($food_trucks_src as $fts) {
			if ($fts["status"] === "APPROVED") {
				$yelp_data = get_yelp_data_for_truck(
					$fts["applicant"],
					$fts["latitude"],
					$fts["longitude"]
				);
				if ($yelp_data) {
					$food_trucks[$fts["applicant"]] = array(
						"vendorname" => $yelp_data["name"],
						"fooditems" => $fts["fooditems"],
						"latitude" => $fts["latitude"],
						"longitude" => $fts["longitude"],
						"thumbnail" => $yelp_data["image_url"],
						"phone" => $yelp_data["display_phone"],
						"yelp_url" => $yelp_data["url"],
					);
				}
			}
		}
		return $food_trucks;
	}
	$food_truck_data = get_food_truck_data();
	// var_dump($food_truck_data);
?>

<!DOCTYPE html>
<html>
	<head>
		<title>SF Food Trucks</title>
		 <style type="text/css">
		 	body {
		 		font-family: Arial, sans-serif;
		 		text-align: center;
		 	}
		 	h1, hr {
		 		width: 800px;
		 		margin: 0 auto;
		 	}
		 	#map {
		 		width: 800px;
		 		height: 500px;
		 		margin: 10px auto;
		 	}
		 </style>
		 <script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
		 <script type="text/javascript">
			function initialize() {       
				var mapOptions = {
					zoom: 12,
					center: new google.maps.LatLng(37.7749300, -122.4194200), // Center of SF
					mapTypeId: google.maps.MapTypeId.ROADMAP,
				};
				var map = new google.maps.Map(document.getElementById('map'), mapOptions);
				<?php
					foreach ($food_truck_data as $name => $data) {
				?>
						var friendLatLng = new google.maps.LatLng(
							"<?php echo $data['latitude']; ?>",
							"<?php echo $data['longitude']; ?>"
						);
						var marker = new google.maps.Marker({
						  position: friendLatLng,
						  map: map,
						  title: "<?php echo $name; ?>"
						});
				<?php 
					}
				?>
			};
			google.maps.event.addDomListener(window, 'load', initialize);
		</script>
	</head>
	<body>
		<h1>SF Food Trucks</h1>
		<hr />
		<div id="map"></div>
	</body>
</html>