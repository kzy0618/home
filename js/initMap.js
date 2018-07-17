// Initialize and add the map, this function is always fired earlier than anything else
function initMap() {

    // THIS AREA IS FOR TESTING PURPOSE ONLY
    // ALL PRODUCTION CODE GO INTO SCRIPT.JS
    // !! NO PRODUCTION CODES HERE !!


    // The location of Uluru
    let uluru = {lat: -25.344, lng: 131.036};
    // The map, centered at Uluru
    // noinspection JSUnresolvedVariable
    let map = new google.maps.Map(
        document.getElementById('map'), {zoom: 4, center: uluru});
    // The marker, positioned at Uluru
    // noinspection JSUnresolvedVariable
    let marker = new google.maps.Marker({position: uluru, map: map});

    $(document).ready(() => {
        console.log("test if document ready can be listened here");
        console.log("yes it can");
    })
}