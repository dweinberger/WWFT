
var gPhotoCtr = 0;
var gAllPhotos = new Array();
var gCtr = 0;

function init(){
// 
// 	document.getElementById("startbtn").addEventListener("click", function(){
// 		loadJson();
// 		});


}


// 1. ---------  // get the list of the top tags, the ones in use and turn into an array. 
function loadJson(){
	var topTags;
	

	// Then build buttons
	var div,j;
	var tagfile = "mostUsedTags2.txt";
	$.get(tagfile, function(txt) {
  			topTags = txt.split('\n');
  			//topTags = topTags.split('\n');
  			// create the tag buttons
  			var tagsdiv = document.getElementById("tags");
  			for (j=0; j < topTags.length; j++){
  				var outerspan = document.createElement("span");
  				var tagspan = document.createElement("span");
  				var countspan =  document.createElement("span");;
  				var id = "btn" + j;
  				$(outerspan).attr({"class" : "tagbtn","id" : id});
  				$(tagspan).text(topTags[j] + " ");
  				$(tagsdiv).append(outerspan);
  				$(outerspan).click(function(){
  					showImages(this);
  				});
  				$("#tags").append(outerspan);
  				$(outerspan).append(tagspan);
  				$(outerspan).append(countspan);
  				$(countspan).attr({"id" : "ct" + j});
  				$(countspan).text("0");
  			}
  		}).done(function(){
  				processJsonFiles(topTags);
  			});  	
  	
}	

// 2. ------------- go through the json files to build big array of tags and photos
function processJsonFiles(topTags){
	
	// tell it how many json file
	numberOfJsonFiles = 33; // (actual number, including 0)
	// create array of tag counts. index corresponds to topTags of tags
	var tagCtrArray = new Array();
	// initializes the tag counter array
	for (var z=0; z < topTags.length; z++){
		tagCtrArray[z] = 0;
	}
	
	for (var i =0; i < numberOfJsonFiles; i++){
		var jsonFileName = "working_set/json/" + i + ".json"
		
		$.get(jsonFileName, function(rawjson) {
			$("#status").text($("#status").text() + " " +  gCtr);
			gCtr++;
  			// go through this batch of photo records
  			rawjson.forEach(function(photo) {
  			
  				var tags = photo["tags"]; // all tags for this photo
  				// add used tags to tag counter array
  				for (var j=0; j < tags.length; j++){
  					gPhotoCtr++;
  					var onetopTags = new Array;
  					onetopTags["tags"] = photo["tags"];
  					onetopTags["tagstring"] = photo["tags"].join(",");
  					onetopTags["photo"] = photo["filename"];
  					gAllPhotos.push(onetopTags);
  					
  				} // for each tag
  				}); // for each pohoto in a batch
  				
  			}, 'json') // for each json file
  			.done(function(){
  				updateButtons(topTags);
  				
  				}
  			)
  			
  			
		} // get each json file
		
		$("#spinner").fadeOut();
	}

// 3. ------------ Update times tags used	
function updateButtons(topTags){
	// Go through all photos counting the times the 200 top tags are used
	var i,tags,tag;
	var tagCtrArray = new Array();
	// init the array
	for (x=0; x < topTags.length; x++){tagCtrArray[x] = 0;}
	// look at each photo
	for (i=0; i < gAllPhotos.length; i++){
		// get the tag array for that photo
		tags = gAllPhotos[i]["tags"];
		// go through each phto's tag array
		for (var j=0; j < tags.length; j++){
			tag = tags[j];
			var curhtml = $("#photodiv").html();
		//	$("#photodiv").html(curhtml + " |" + tag + "|");
		// is this a top tag?
			var tagindex = jQuery.inArray(tag,topTags);
			// This is not one of the top tags we care about
			if (tagindex == -1){
				//alert("error with tag:" + tag);
				//break;
				var nope = tag;
				}
			// we have a top tag
			else{
				//var tot =tagCtrArray[tagindex];
				tagCtrArray[tagindex]++; // = tot++;
				}
			} // gone through tags for one photo
  	} // gone through allphotos
  	
  	
  	
  	// update the display
  	for (i=0; i < tagCtrArray.length; i++){
  		$("#ct" + i).text(tagCtrArray[i]);
  	}
  						
}

function showImages(btn){
	
	// clear existing photos
	$("#photodiv").html("");
	var tags;
	
	// get the tag from the button
	var tag = $(btn).children(":first").text();
	// strip last space
	tag = tag.substr(0, tag.length - 1);
	
	// find all instances of it
	for (var i=0; i < gPhotoCtr; i++){
		// get all the tags for one photo
		tags = gAllPhotos[i]["tags"];
		// is the button tag in the tag array of this photo
		if ( $.inArray( tag, tags ) > -1){
			var div = document.createElement("div");
			$(div).attr({"class" : "photo"});
			var html = "<p><img src='working_set/" + gAllPhotos[i]["photo"] + 
				"'></p><p>Tag: " + tag + "<br>" + gAllPhotos[i]["tagstring"] + "<br>Filename:"  + gAllPhotos[i]["photo"] + "</p>";
			$(div).html(html);
			$("#photodiv").append(div);
		}
	
	}
	
	
	//$("#photos").text(tag);
	//alert("|" + tag + "|");

}