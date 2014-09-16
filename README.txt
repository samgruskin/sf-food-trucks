README
Project: San Francisco's Food Trucks
Live Demo: http://samgruskin.com/experimental/sf-food-trucks/index.html
Github Repo: https://github.com/samgruskin/sf-food-trucks
Author: Samantha Gruskin <samgruskin@gmail.com>
Short Description: An interactive map that displays the locations of all verified food 
trucks in San Francisco.  
Additional Info: The truck location data is pulled from DataSF's Mobile Food Permit 
database, and then each of these vendors is cross-referenced with Yelp's Business listings 
to ensure that the food truck/stand is actually open and is legit (if it's a good food 
truck, people should've reviewed it!). By pulling Yelp data, we are also able to have 
additional information about the business readily available (phone number, Yelp business 
URL, etc.) which is then displayed in the information card when a food truck pin is 
clicked on. 

1. Features:
	a. Info cards for each food truck pin containing a thumbnail, phone number, food 
	offerings description, and a business URL.
	b. Current location finder button.
	c. San Francisco-centric location search.
	
2. Known issues:
	a. Load time: This could be improved (with more time working on this project) by 
	storing this data in a database on my own server, and refreshing this database in the 
	background via a cron job which runs daily (the food truck listings probably don't 
	change often enough to justify pulling this data every single time the application 
	is run!)
	
3. File List:
oauth.php // PHP OAuth Library
food_truck_data.php
index.html
images/
	food_truck_icon.png
	geoloc.png
	loading.gif
