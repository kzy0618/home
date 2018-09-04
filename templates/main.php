<?php
//script('home', 'leaflet');
//script('home', 'leaflet-src');
//script('home', 'leaflet.label');
//
//// script('home', 'cities');
//
style('home', 'style');
style('home', 'scrollbar');
script('home', 'markerclusterer');
//style('home', 'leaflet');
//style('home', 'leaflet.label');


?>

<div id="app">
<!--    <div id="map"></div> code from old home, for leaflet -->
    <!--The div element for Google map
		 	Remember to give it a height and width in your style css -->
    <div id="map"></div>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA1W9m44Rv3qXbCDJ3PqhUXtRvsdnJ80oM&callback=initMap">
    </script>

<!--	// TODO : Change the path to specify the location where you have saved the same file.-->
<!--	<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>-->

	<?php print_unescaped($this->inc('part.content'))?>
    <!--     <button id="login">Login</button> code from old home, no clue -->
</div>

<?php
// custom scripts
script('home', 'script');
// script('home', 'initMap'); // For testing purpose only, NOT NEEDED FOR PRODUCTION, PRODUCTION CODES GO INSIDE SCRIPT.JS
?>
