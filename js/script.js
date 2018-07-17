// noinspection JSUnusedGlobalSymbols
/**
 * Author: Jonathan Kong, Mason Shi
 *  Copyright: Jonathan,Mason 2018
 */

// Initialize and add the map, this function is always fired earlier than anything else
// Hence, ANY other functions (e.g., owncloud's ready listener) MUST BE WRAPPED inside 'initMap()' which will be called by
// Google.
function initMap() {

    // PUT GLOBAL SETTINGS HERE

    // The location of Uluru
    let auckland = {lat: -36.8485, lng: 174.7633}; // number of decimals does not matter too much
    // The map, centered at AKL
    // noinspection JSUnresolvedVariable
    let map = new google.maps.Map(
        document.getElementById('map'), {zoom: 8, center: auckland});

    (function (OC, $) {

        $(document).ready(() => {
            console.log("Map Init");
            console.log("Hello World");

            // Add a marker, AFTER document is ready, positioned at Uluru
            // noinspection JSUnresolvedVariable
            let marker = new google.maps.Marker({position: auckland, map: map});

            // TODO : SEND THE FIRST REQUEST
            // TODO : POPULATE MARKERS
            // TODO : ADD LISTENERS AND OTHER STUFF FOR EVERY MARKERS

        });

    })(OC, jQuery);

}

// (function (OC, $) {
//
//     // initMap() is always called BEFORE this anonymous function ever fired, can't put it here
//
//     // $(document).ready(() => {
//     //     console.log("Map Init");
//     //
//     //     // initMap() is always called BEFORE $(document).ready(), can't put it here either
//     //
//     // });
//
// })(OC, jQuery);