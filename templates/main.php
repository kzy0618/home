<script src="http://ec2-52-64-29-123.ap-southeast-2.compute.amazonaws.com/apps/home/js/cities.geojson"></script>

<?php
script('home', 'script');
script('home', 'leaflet');
script('home', 'leaflet-src');
// script('home', 'cities.geojson');


style('home', 'style');
style('home', 'leaflet');

?>

<div id="app">
<div id="map"></div>
<!--     <button id="login">Login</button>
 --></div>
