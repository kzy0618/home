<script src="data/cities.geojson"></script>

<?php
script('home', 'script');
script('home', 'leaflet');
script('home', 'leaflet-src');
script('home', 'cities');


style('home', 'style');
style('home', 'leaflet');

?>

<div id="app">
<div id="map"></div>
<!--     <button id="login">Login</button>
 --></div>
