=== Mapworks ===
Contributors: Prakash
Tags: business locations, admin, dealer locator, dealer locater,  post, store locater, mapping, mapper, google, google maps, locator, ajax, shop locator, shop finder, shortcode, location finder, places, widget, proximity, zipcode locator, geocoder, stores, plugin, maps, coordinates, latitude, longitude, posts, geo, geocoding, jquery, shops, page, zipcode, zip code, zip code search, store finder, address map, address location map, map maker, proximity search, map creator, mapping software, map tools, zip code locator, mapping tools, locator maps, map of addresses, map multiple locations, wordpress locator, zipcode search, locator map
Requires at least: 3.3

A WordPress-integrated map & location management interface. Quickly create street locator maps

== Description ==
Power your site with this WordPress-integrated map making & location management system possessing mapping tools to create store locators, store finders, and other location address maps. Manage and display a few or thousands of your important stores, points of interest, or product locations anywhere on Earth using Google Maps. 
Its strength is best seen in its flexibility to allow you to easily manage any number of locations from your WordPress admin interface and the several addons featuring top-level features that are available to further boost its capabilities. Also referred to as an address map, address location map, locator map, store finder, dealer locator (locater), shop finder. 
mapwidget allow you to search any location from front end.

Example usage:




		<?php  
		if(function_exists('mapworks') ){
                 mapworks($_POST['smap']);
		}
		?> 

		<?php  
		if(function_exists('mapworks') ){                 
		 print do_shortcode('[mapworks stext="'.$_POST['smap'].'"]');
		}
		?> 

