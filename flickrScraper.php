
<?php

// ******************TESSTING SCRAPING ONE PAGE &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

/* Last updated with phpFlickr 1.3.2
 *
 */
 
 /*
 continuation (Optional)
Using this parameter indicates to the server that the client is using the new, continuation based pagination rather than the older page/per_page based pagination. The first request must pass the "continuation" parameter with the value of "0". The server responds back with a response that includes the "continuation" field along with the "per_page" field in the response. For the subsequent requests, the client must relay the value of the "continuation" response field as the value of the "continuation" request parameter. On the last page of results, the server will respond with a continuation value of "-1".
*/

date_default_timezone_set('America/New_York');
echo "<h3 style='color:red'>Started processing JSON at" . 	date("H:i:s") . "</h3>";

 
require_once("phpFlickr.php");
$apikey = "7d0fd6c4dfca52f526030edcaa33d19b";
$userid = "35468159510@N01";
$startpage = 1;
$numOfPages=100; // how many pages to retrieve
$photosPerPagetoFetch = 500; // max 500
$photosPerFile = 100;
$photosInCurrentFileCtr = 0;
$totalPhotosCtr=0;
$totalPhotosWithTags = 0;

// cycle through the number of pages
for ($batchNumber=0; $batchNumber < $numOfPages; $batchNumber++){

	// initialize one page's array
	 $oneBatchOfPhotos = array();
	
	// create the query
	//works:
	//https://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=7d0fd6c4dfca52f526030edcaa33d19b&user_id=35468159510@N01&format=rest
	//$queryurl = "https://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=7d0fd6c4dfca52f526030edcaa33d19b&format=rest&extras=tags&is_commons=1";
	$queryurl = "https://api.flickr.com/services/rest/?method=flickr.photos.getRecent&safe_search=1" .
		"&page=" . $startpage .
		"&per_page=" . $photosPerPagetoFetch .
		"&content_type=1&api_key=7d0fd6c4dfca52f526030edcaa33d19b&format=json&extras=tags&privacy_filter=1&nojsoncallback=?";
	echo "<br>url: $queryurl";
	$startpage++;
	
	// query the API
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $queryurl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$ret = curl_exec($ch);
 
	$json = json_decode($ret,true);
	// echo "PHOTOS COUNT: " . count($json) . "<br>";
	//print_r($json);

	$all = $json[photos];
	$allPhotosInBatch = $all["photo"];
	echo "PHOTOS ON Page $batchNumber COUNT: " . count($allPhotosInBatch) . "<br>";
	$item_in_batch = 0;
	$accepted_items_in_batch = 0;
	echo "<h3>################ NEW PAGE: $batchNumber ############</h3>";

	// -------- cycle through one batch (download) of photos
	foreach ($allPhotosInBatch as $photo) {
		echo "<br>";
		if ($photo["tags"] == "" ){echo  "NO TAG = ";}
		echo "======= batch# $batchNumber item# $item_in_batch ================<br>";
		$item_in_batch++;
		// counters across all pages
		$totalPhotosCtr++;
		// actual url of the photo
		$purl = "http://farm" . $photo["farm"] . ".static.flickr.com/" . $photo["server"] . "/" . $photo["id"] .  "_" . $photo["secret"] . "_m.jpg";
		//print_r($photo);
		// echo info about each photo
		echo "OWNER: " . $photo["owner"] . "<br>";
		echo "FARM: " . $photo["farm"] . "<br>";
		echo "server: " . $photo["server"] . "<br>";
		echo "secret: " . $photo["secret"] . "<br>";
		echo "id: " . $photo["id"] . "<br>";
		echo "title: " . $photo["title"] . "<br>";
		echo "url: " . $purl . "</br>";
		$tagstring = $photo["tags"];
		echo "tags: " . $photo["tags"] . "<br>";
		
		// -- If there are tags, then save the photo
		if  ($photo["tags"] != "" ) {
			// add it to collection to be saved
			$totalPhotosWithTags++; 
			// create a file name so we can add it to the json
			// save the photo
			$filenameout ="images2/image_" . $batchNumber . "-" . $accepted_items_in_batch . ".jpg";
			$accepted_items_in_batch++;
			echo "FILENAME:" . $filenameout . "<br>";
			$tagArray = explode(" ",$tagstring);
			
			$onephoto = array(	
					"owner" => $photo["owner"],
					"id" => $photo["id"],
					"url" => $purl,
					"title" => $photo["title"],
					"tags" => $tagArray,
					"farm" => $photo["farm"],
					"secret" => $photo["secret"],
					"server" => $photo["server"],
					"filename" => $filenameout
				);
			 array_push($oneBatchOfPhotos, $onephoto);
			 
			$purl = "http://farm" . $photo["farm"] . ".static.flickr.com/" . $photo["server"] . "/" . $photo["id"] .  "_" . $photo["secret"] . "_m.jpg";
			echo "<pre>" . $purl . "</pre>";
			echo "<img src=" . $purl . "><br>";
			
			// Save the photo
			//file_put_contents($filenameout, $purl );
			copy($purl, $filenameout);

			
			$photosInCurrentFileCtr++;
			
			// --- time to write out a file?
			// if ($photosInCurrentFileCtr > $photosPerFile){
// 
// 				// if we have hit the desired number of photos for a file
// 				// then save them all
// 			
// 				$parray = json_encode($oneBatchOfPhotos);
// 				$batchFileName = "images2/" . $batchNumber . ".json";
// 				echo "<h3>Writing out JSON: $batchFileName</h3>";
// 				file_put_contents($batchFileName, $parray);
// 				$photosInCurrentFileCtr = 0;
// 				$oneBatchOfPhotos = array();
			}
		} // gone through all photos in one batch
		
	// write out that batch's json. It will only contain
	// info about photos that had tagss
	$batchFileName = "images2/" . $batchNumber . ".json";
	$parray = json_encode($oneBatchOfPhotos, JSON_PRETTY_PRINT);
	echo "<h3>Writing out  JSON: $batchFileName</h3>";
	print_r($parray);
	file_put_contents($batchFileName, $parray);
		
	} // processesd all downloaded batches

		


// write out any remaining photos
// DEBUG: Iss $batchNumber still right here??
if ((1==0) && (count($oneBatchOfPhotos) > 0)){
	$batchFileName = "images2/" . $batchNumber . ".json";
	$parray = json_encode($oneBatchOfPhotos, JSON_PRETTY_PRINT);
	echo "<h3>Writing out Remaining JSON: $batchFileName</h3>";
	echo "remaining json:<br>";
	print_r($parray);
	
	file_put_contents($batchFileName, $parray);
}

echo "<br>*********************TOTAL Batchess: $batchNumber TOTAL PHOTOS SEEN: $totalPhotosCtr WRITTEN: $totalPhotosWithTags";
echo "<h3 style='color:red.>ENDED processing JSON at" . 	date("H:i:s") . "</h3>";
	
?>
