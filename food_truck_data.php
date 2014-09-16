<?php
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
		// Ensure a business listing is returned and the location is not closed 
		// permanently.
		if (array_key_exists("businesses", $results) && 
			!$results["businesses"][0]["is_closed"]) {
			return $results["businesses"][0];
		}
		return null;
	}
	
	function get_food_truck_data() {
		$food_trucks_src = get_food_trucks();
		// Only keep food truck data we care about.
		$food_trucks = array();
		foreach ($food_trucks_src as $fts) {
			if ($fts["status"] === "APPROVED") {
				$yelp_data = get_yelp_data_for_truck(
					$fts["applicant"],
					$fts["latitude"],
					$fts["longitude"]
				);
				// If this truck has a yelp listing, it should actually exist :-)
				if ($yelp_data) {
					$food_trucks[$fts["applicant"]] = array(
						"vendorname" => $yelp_data["name"],
						"fooditems" => $fts["fooditems"],
						"latitude" => $fts["latitude"],
						"longitude" => $fts["longitude"],
						"thumbnail" => $yelp_data["image_url"],
						"phone" => $yelp_data["phone"],
						"displayphone" => $yelp_data["display_phone"],
						"yelpurl" => $yelp_data["url"],
					);
				}
			}
		}
		return $food_trucks;
	}
	$food_truck_data = get_food_truck_data();
	echo json_encode($food_truck_data);
?>