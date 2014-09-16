sf-food-trucks
==============

<b>An interactive map that displays the locations of all verified food trucks in San Francisco.</b>
Live Demo: http://samgruskin.com/experimental/sf-food-trucks/index.html

==============

Project: San Francisco's Food Trucks<br>
Author: Samantha Gruskin <sam@samgruskin.com><br>

Additional Info: The truck location data is pulled from DataSF's Mobile Food Permit 
database, and then each of these vendors is cross-referenced with Yelp's Business listings 
to ensure that the food truck/stand is actually open and is legit (if it's a good food 
truck, people should've reviewed it!). By pulling Yelp data, we are also able to have 
additional information about the business readily available (phone number, Yelp business 
URL, etc.) which is then displayed in the information card when a food truck pin is 
clicked on. 

1. Features:<br>
	- Info cards for each food truck pin containing a thumbnail, phone number, food 
	offerings description, and a business URL.<br>
	- Current location finder button.<br>
	- San Francisco-centric location search.
	
2. Known issues:<br>
	- Load time: This could be improved (with more time working on this project) by 
	storing this data in a database on my own server, and refreshing this database in the 
	background via a cron job which runs daily (the food truck listings probably don't 
	change often enough to justify pulling this data every single time the application 
	is run!)
	
3. File List:<br>
oauth.php // PHP OAuth Library<br>
food_truck_data.php<br>
index.html<br>
images/<br>
	- food_truck_icon.png<br>
	- geoloc.png<br>
	- loading.gif