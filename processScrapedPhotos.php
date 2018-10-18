<?php

// Count tags gathered from flickr scraper
// Trying to find tags most often used in the batch
// Return an array containing only tags used n times
// Assumes a set of json files of the form 0.json that contain data
//   about public photos scraped from Flickr. See flickrScraper.php

// need extra processing time for large collections
date_default_timezone_set('America/New_York');
echo "<h3>Started processing JSON at" . 	date("H:i:s") . "</h3>";
ini_set('max_execution_time', 300);

// globals
$allTags= array(array("tag" => "nulltest", "ctr" => 1)); // global array of all tags
$sortedTags = array();


//print_r($allTags . "<br>");
//echo $allTags[0]["tag"] . "--<br>";

 "<br>Count alltags:" . count($allTags) . "<br>";

function find_array_key($tag1){
//echo "at:";
global $allTags;
//print_r($allTags);
	$done = false;
	$i = 0;
	//echo "<br>cCount alltags:" . count($allTags) . "<br>";
	foreach ($allTags as $tagpair){
		$tag2 = $tagpair["tag"];
		//echo "tag2: $tag2 | $tag1";
		if ($tag1 == $tag2){
			echo  ". ";
			break;
		}
		else{
			$i++;
			//if ( ($i % 50) == 0) {echo "<br>*";}
		}
	}

// if reached end without a match
if ($i >= count($allTags)){
	$i = -1;
}
//echo "<br>returning $i";
return $i;

}

function processTags($json){
	// creates a global array of all tags
	
	global $allTags;
	foreach ($json as $photo){
		$tags = $photo["tags"];
		//print_r($tags);
		foreach ($tags as $tag){
			//echo"<br>* Processing: $tag<br>";
				$tagmatch = find_array_key($tag);
				if ( $tagmatch > -1){
					$allTags[$tagmatch]["ctr"] = $allTags[$tagmatch]["ctr"] + 1;
					//echo "<br>$tag found. count: " . $allTags[$tagmatch]["ctr"] . "<br>";
				}
				else{
					array_push($allTags, array("tag" => $tag, "ctr" => 1));
				}
		}
	
	}	
}

function sortTags(){
	// sort AllTags by value of "ctr";
	//https://stackoverflow.com/questions/1597736/how-to-sort-an-array-of-associative-arrays-by-value-of-a-given-key-in-php
	global $sortedTags;
	global $allTags;
	
	//$price = array();
	foreach ($allTags as $key => $row)
	{
   	 	$sortedTags[$key] = $row['ctr'];
	}
array_multisort($sortedTags, SORT_DESC, $allTags);
	
}
	
// ******************* BEGIN HERE ***********************
// *******************************************************

// 1. ===== Read the json files to build array of tags
if (1 == 0){
	echo "STARTING";
	$readingFiles = true;
	// IF ALREADY compiled the tags, then
	$readingFiles = true;
	$i = 0;
	$j = "";
	//$jarray = array();
	while ($readingFiles){
		$fname = "images2unreduced/" . $i . ".json";
		echo "<br>processing file $fname";
		if (file_exists($fname)){
			$j = json_decode(file_get_contents($fname), true);
			//$jarray = array_merge($jarray, $j);}
			//echo "<br>Reading $i.<br>";
			//echo $j;
			//echo "- $i ";
		
			// —  build array of tags with counter for times used
			processTags($j);
			$i++;
		}
		else{
			$readingFiles = false;
			echo "<br>+++ Done reading json. Read $i of them.";
		}

	}

	echo "<h3>Ended processing JSON at" . 	date("H:i:s") . "</h3>";

}	
// 2. ==== Read all tags in from saved file
	if (1 == 0){
		$allTags = array();
		$handle = fopen("list_of_tags2.txt", "r");
		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				// process the line read.
				$temppairstr = explode(" : ",$line);
				$temppair= array( "tag" => $temppairstr[0], "ctr" => $temppairstr[1]);
				array_push($allTags, $temppair);
   	 			}
    	fclose($handle);
		} 
	else {
		// error opening the file.
		echo "<h3> Could not open list_of_tags.txt</h3>";
	}
	echo "<hr>Count of alltags after reading fromn file: " . count($allTags) . "<br>";
	print_r($allTags[0]);
	}

// 2. ====== Sort the tags by number of times user
   sortTags(); // sorts $allTags into $sortedTags (both are global arrays)
	/*
	print_r($sortedTags);	
		debugging printout */
		echo "<hr>";
		 foreach($allTags as $pair){
			if ($pair["ctr"] > 4){
				echo "<br>" . $pair["tag"] . ": " . $pair["ctr"];
			}
		 }
	
	echo "<hr>";
	
// 3. ====== Reduce tag array to tags used more than n times
if (1 == 0){
	$usedTags = array();
	foreach($allTags as $pair){
		if ($pair["ctr"] > 14){
			//echo "<br>" . $pair["tag"] . ": " . $pair["ctr"];
			// did we already push this one?
			if ( (in_array($pair["tag"], $usedTags) !== true) && 
				($pair["tag"] !== "instagram")  
				&& ($pair["tag"] !== "ifttt")){
					$tg = $pair["tag"];
					array_push($usedTags, $tg);
					echo " [" . $tg . "] ";
			}
			array_push($usedTags, $tg);
		}
    }
    // display
    $i = 1;
    echo "<br>Number of usedTAgs=" . count($usedTags);
    echo "<h3>UsedTags</h3>";
    foreach($usedTags as $tag){
    	echo $i . ": " . $tag . "<br>";
    	$i++;
    }
}
    
// 4. ========= Write out a file of the reduced tags
	if (1 == 0){
		//$allTheTags = print_r($allTags,true);
		
		$tf = fopen("/Users/dweinberger/Sites/flickr-cc0-scraper/mostUsedTags2.txt","a");
		foreach ($usedTags as $tag){
			//fwrite($tf,print_r($usedTags,true));
			fwrite($tf, $tag . PHP_EOL);
		}	
		fclose($tf);
		echo "<h3>File of tags written to mostUsedTags2.txt</h3>";
	}


// 5. ====== Create new JSON of photos with an accepted tag, with full metadata

// Go through all the json files again, looking at each images tags.
// Throw out any tags not on the reduced list.
// If none of the tags are on the reduced list, through out the entire image.
// Build a new set of json files containing only images with the reduced tags,
//     with their full metadata
// Note that the original images and json are now in ./images2unreduced,
//     and the final set of images will be in ./images, json in ./json

if (1 == 1){
	echo "STARTING BUILD OF FINAL JSON AND IMAGE SET";
	/* 
	"owner":"142968737@N06",
      "id":"43400868625",
      "url":"http:\/\/farm2.static.flickr.com\/1849\/43400865225_87a4bc8aa2_m.jpg",
      "title":"Is there a city you would secretly love to live in and have never told anyone about? Let me know in the comments below!! \ud83d\ude03 Throwback to this beautiful view in my beloved Salzburg \ud83d\ude0d\ud83d\ude0d",
      "tags":[  
         "ifttt",
         "instagram"
      ],
      "farm":2,
      "secret":"f456c82a61",
      "server":"1896",
      "filename":"images\/image_53-0.jpg"
   },
	*/
	$readingFiles = true;
	$i = 0;
	$j = 0;
    // read in used tags
    $tagsString = file_get_contents("mostUsedTags.txt");
    $usedTags = explode(PHP_EOL, $tagsString);
	echo "<BR>usedtags:<br>";
	print_r($usedTags);
	echo "<BR> end of used tags</br>";
	echo "<h3>Began processing JSON at" . 	date("H:i:s") . "</h3>";
	$stillReadingFiles = true;
	while ($readingFiles){
		$jsonOfPhotosWithTags_oneBatch = array();
		$originalJsonFileName = "images2unreduced/" . $j . ".json";
		echo "<h4>processing file $originalJsonFileName</h4>";
		$stillReadingFiles = file_exists($originalJsonFileName);
		// are we done reading json files?
		if ($stillReadingFiles == false){
			echo "<h2>Done reading files. Read $j of them.</h2>";
		}
		// ---- GET JSON FILE TO READ
		if ($stillReadingFiles == true){
			$allPhotosInOneJSONfile = json_decode(file_get_contents($originalJsonFileName), true);
		
			// ---- LOOK AT EACH IMAGE IN A JSON FILE
			foreach ($allPhotosInOneJSONfile as $photo){
				$keepThisImage = true;
				$tags = $photo["tags"];
				//print_r($tags);
				// look at each tag for this image
				$reducedTags = array();
				foreach ($tags as $tag){
					// is there a tag key?
					$tagmatch = find_array_key($tag);
					// if ( $tagmatch > -1){ // don't need b/c if no $tags, no foreach
					// do any tags match the reduced set?
					if (in_array($tag, $usedTags)){
							array_push($reducedTags, $tag);
							//echo "<br>$tag found. <br>";
						}
				} // looked at all tage for one image
				// did we not find any reduced tags?
				if (count($reducedTags) < 1){
					$keepThisImage = false;
				}
				if ($keepThisImage == false){
					// Add "SKIP-" to any file without tags
					$imageOriginalFileName = $photo["filename"];
					$fileNoPath = basename($imageOriginalFileName);
					$skippedName = "images2/" . "SKIP-" . $fileNoPath;
 					echo "<br>SKIPPING Original file: $imageOriginalFileName NEW: $skippedName <br>";
					rename($imageOriginalFileName, $skippedName);
				}
				if ($keepThisImage){
					array_push($jsonOfPhotosWithTags_oneBatch, $photo);
				
				
				}
				// if ($keepThisImage){
// 					// replace the photos tags with the reduced set
// 					$photo["tags"] =  $reducedTags;
// 					// copy the image to new directory
// 					$imageFileName = $photo["filename"];
// 					$imageOriginalFileName =$imageFileName;
// 					echo "<br>Original file: $imageOriginalFileName <br>";
// 					// str_replace(find,replace,string)
// 					$newImageFileName = str_replace( "images2","finalImageSet", $imageOriginalFileName);
// 					echo "<br>Imagefile name NEW: $newImageFileName | ORIGINAL: $imageOriginalFileName <br>";
// 					// copy(source, destination)
// 					copy($imageOriginalFileName,  $newImageFileName );
// 				}
			} // finished with the images in this json file
				;
				echo "<h3>Writing out JSON: $batchFileName</h3>";
// 				$batchFileName = "FinalmageSet/" . $i  . ".json";
// 				file_put_contents($batchFileName, $allPhotosInOneJSONfile);
				// copy the image to new folder
			
				if ($j < 210){
					
					// create new json
					
					$jsonForPrint = json_encode($jsonOfPhotosWithTags_oneBatch, JSON_PRETTY_PRINT);
					$batchFileName = "json/" . $j . ".json";
					file_put_contents($batchFileName,$jsonForPrint);
					
				}
				$j++;
					
			} // if there was a file at all
			else {$readingFiles = false;}
			
		
			

	} // no more files

	echo "<h3>Ended processing JSON at" . 	date("H:i:s") . "</h3>";

}


?>