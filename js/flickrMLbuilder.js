
// Globals

gImageTag = new Array();
gOneHots = new Array();
gOneOneHot = new Array();
gOneHotIndex = 0;
g200Tags = new Array();

// these are the real ones:
var gPhotosWithOneTag = new Array();
var gSyncedTagList = new Array(); 
// deprecated:
gTagAssignments = new Array();

function init(){
	
	a = "inArray;";

	// get the tags
	$.get("mostUsedTags2.txt", function(rawtags) {
		var lines = rawtags.split("\n");
		for (var i =0 ; i < lines.length; i++){
			g200Tags.push( lines[i]);
		}
		});
}

function buildOnehot(){
	$("#onehotstatus").text("Building onehots...");
	var numbOfTags = g200Tags.length;
	var i,j;
	for ( i = 0; i < numbOfTags; i++){
		// create array of 200approx zeroes
		var gOneOneHot = new Array(); // init single onehot array
		for (j=0; j < numbOfTags; j++){
			gOneOneHot[j] = 0;
		}
		// replace one zero with a 1
		gOneOneHot[i] = 1;
		// add this onehot array of 200 to the list of onehots
		gOneHots.push(gOneOneHot);
	}
	$("#onehotstatus").text("DONE. Built " + numbOfTags +  " onehots.");
}



function loadData(){
	// we want arrays: 
		// 1. single tags used, expressed by the index of its onehot
		// 2. synced array of photo filenames
		// 3. backup synced array of photo filenames + all json data 
		//   (Not needed but you never know)
	
		

  // tell it how many json file
	var numberOfJsonFiles = 33; // (actual number, including 0)
	// create array of tag counts. index corresponds to topTags of tags
// 	var tagCtrArray = new Array();
// 	// initializes the tag counter array
// 	for (var z=0; z < topTags.length; z++){
// 		tagCtrArray[z] = 0;
// 	}

	var tagApplicationsCtr = 0;
	
	// —--  Read each json file
	for (var i =0; i < numberOfJsonFiles; i++){
		var jsonFileName = "working_set/json/" + i + ".json"
		
		$.get(jsonFileName, function(rawjson) {
			//$("#status").text($("#status").text() + " " +  gCtr);
			//gCtr++;
			
			
  			// --- go through this batch of photo records
  			rawjson.forEach(function(photo) {
  				var thisPhotosTags = photo["tags"]; // all tags for this photo
  				// Go through this photo's tags looking for ones in top 200
  				for (var j=0; j < thisPhotosTags.length; j++){
  					var oneTagFromPhoto = thisPhotosTags[j];
  					// is this tag a top200?
  					var tagIndex = jQuery.inArray(oneTagFromPhoto, g200Tags);
					if ( tagIndex > -1){
						var tempPair = new Array(1);
						tempPair[0] = new Array(2);
						tempPair[0][0] = tagIndex;
						tempPair[0][1] = photo["filename"]; 
						gTagAssignments.push(tempPair);
						gPhotosWithOneTag.push(photo["filename"]);
						gSyncedTagList.push(tagIndex);
						// oneTopTags["tag"] = oneTagFromPhoto;
// 						oneTopTags["tags"] = photo["tags"];
// 						//var id = photo["id"];
// 						var ty = (typeof photo["id"]);
// 						if (ty !== "string"){
// 							break;
// 						}
// 						var purl = "http://farm" + photo["farm"] + ".static.flickr.com/" + photo["server"] + "/" + photo["id"] +  "_" + photo["secret"] + "_m.jpg";
// 						oneTopTags["flickrUrl"] = purl;
// 						var s = photo["tags"].join(", ");
// 						s = s.replace(oneTagFromPhoto, "<b>" + oneTagFromPhoto + "</b>");
// 						oneTopTags["tagstring"] = s;
// 						oneTopTags["photo"] = photo["filename"];
// 						gAllTagsPhotoInfo.push(oneTopTags);
					}
  				}
  			}); // for each pohoto in a batch
  				
  			}, 'json') // for each json file
  			.done(function(){
  				//updateButtons(topTags);
  				$("#loadstatus").text("Created array (gTagAssignments) of " + gTagAssignments.length + " uses of top 200 tags. Also number of gPhotos with One Tag:" + gPhotosWithOneTag.length);
  				
  				}
  			)
  			
  			
		} // get each json file
		
		//$("#spinner").fadeOut();
}

