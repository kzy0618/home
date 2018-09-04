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
            let baseUrl = OC.generateUrl("/apps/home");
            // Add a marker, AFTER document is ready, positioned at Uluru
            // noinspection JSUnresolvedVariable

            let processedRecordings = [[]];

            $.get(baseUrl+"/recordings").done(function(recordings){
                console.log(recordings);

                let processedRecordings = [];
                for(let i = 0;i < recordings.length;i++){
                    let ifsame = false;
                    // console.log(processedRecordings);
                    if(recordings[i].isStandalone == 1) {
                        for (let j = 0; j < processedRecordings.length; j++) {
                            let ary = processedRecordings[j];
                            // console.log("DEBUG INFORMATIONL PROCESSING ARRAY INFO");
                            // console.log(processedRecordings.length);
                            // console.log(ary);
                            if (parseFloat(ary[0].standaloneLat) == parseFloat(recordings[i].standaloneLat) && parseFloat(ary[0].standaloneLon) == parseFloat(recordings[i].standaloneLon)) {
                                ary.push(recordings[i]);
                                ifsame = true;
                            }
                        }
                        if (!ifsame) {
                            let ary = [];
                            // console.log("DEBUGING INFORMATION");
                            ary.push(recordings[i]);
                            // console.log(ary);
                            processedRecordings.push(ary);
                            // console.log(processedRecordings);
                        }
                    }
                }
                console.log(processedRecordings);
                // Add some markers to the map.
                // Note: The code uses the JavaScript Array.prototype.map() method to
                // create an array of markers based on a given "locations" array.
                // The map() method here has nothing to do with the Google Maps API.
                // let markers = recordings.map(function(recording) {
                //     let city,label;
                //     if(recording.isRepresentative == 1){
                //         city = {lat: parseFloat(recording.suburbLat), lng: parseFloat(recording.suburbLon)};
                //         label = "R";
                //     }
                //     if(recording.isStandalone == 1){
                //         city = {lat: parseFloat(recording.standaloneLat), lng: parseFloat(recording.standaloneLon)};
                //         label = "S";
                //     }
                //     if(city != undefined) {
                //         let contentString = '<div class="popUpContent">' +
                //             '<div class="cityBelong"> City: ' + recording.cityName + '</div>' +
                //             '<div class="recContent"> Content: ' + recording.content + '</div>' +
                //             '<div class="dateTime"> Datetime: ' + recording.datetime + '</div>' +
                //             '<div class="subrub"> Subrub: ' + recording.suburbName + '</div>' +
                //             '<a href = "' + baseUrl + '/download/' + recording.id + '"> download </a>' +
                //             '</div>';
                //         let infowindow = new google.maps.InfoWindow({
                //             content: contentString
                //         });
                //         let marker = new google.maps.Marker({
                //             position: city,
                //             animation: google.maps.Animation.DROP,
                //             map: map,
                //             label:label
                //         });
                //         marker.addListener('click', function () {
                //             infowindow.open(map, marker);
                //         });
                //         return marker;
                //     }
                // });

                let markers = [];
                recordings.forEach(recording =>{
                   let city,label;
                    if(recording.isRepresentative == 1){
                        city = {lat: parseFloat(recording.suburbLat), lng: parseFloat(recording.suburbLon)};
                        label = "R";
                    }
                    if(city != undefined) {
                        let contentString = '<div class="popUpContent">' +
                            '<div class="cityBelong"> City: ' + recording.cityName + '</div>' +
                            '<div class="recContent"> Content: ' + recording.content + '</div>' +
                            '<div class="dateTime"> Datetime: ' + recording.datetime + '</div>' +
                            '<div class="subrub"> Subrub: ' + recording.suburbName + '</div>' +
                            '<a href = "' + baseUrl + '/download/' + recording.id + '"> Download </a>' +
                            '</div>';
                        let infowindow = new google.maps.InfoWindow({
                            content: contentString
                        });
                        let marker = new google.maps.Marker({
                            position: city,
                            animation: google.maps.Animation.DROP,
                            map: map,
                            label:label
                        });
                        marker.addListener('click', function () {
                            infowindow.open(map, marker);
                        });
                        markers.push(marker);

                    }
                });
                console.log(markers);

                processedRecordings.forEach(standaloneAry =>{
                    let content = '<div class="scrollbar scrollbar-primary">'+ '<div class="force-overflow">';
                    city = {lat: parseFloat(standaloneAry[0].standaloneLat), lng: parseFloat(standaloneAry[0].standaloneLon)};
                    label = "S";
                    let counter = 1;
                    standaloneAry.forEach(recording =>{
                        content = content + '<div class="popUpContent scroll">' +
                        '<div class="cityBelong"> City: ' + recording.cityName + '</div>' +
                        '<div class="recContent"> Content: ' + recording.content + '</div>' +
                        '<div class="dateTime"> Datetime: ' + recording.datetime + '</div>' +
                        '<div class="subrub"> Subrub: ' + recording.suburbName + '</div>' +
                        '<a href = "' + baseUrl + '/download/' + recording.id + '"> Download </a>' +
                        '</div>';
                    });
                    content = content +'</div> </div>';
                    let infowindow = new google.maps.InfoWindow({
                        content: content
                    });
                    let marker = new google.maps.Marker({
                        position: city,
                        animation: google.maps.Animation.DROP,
                        map: map,
                        label:label
                    });
                    marker.addListener('click', function () {
                        infowindow.open(map, marker);
                    });
                    markers.push(marker);
                });

                console.log(markers);
            // <div class="scrollbar scrollbar-primary">
            //         <div class="force-overflow">
            //         </div>
            //         </div>

                // Add a marker clusterer to manage the markers.
                let markerCluster = new MarkerClusterer(map, markers,
                    {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});

            }).fail(function(response){
                console.log(response);
                alert("fail");
            });
        });
    })(OC, jQuery);
}