// ----------------------------------- JQUERY - MAP INIT - BEGIN
let PARIS = new L.LatLng(48.85, 2.35);
let map;

function initializeMap() {
    let map = L.map('mapid', {
        worldCopyJump: true
    });
    let osmUrl = {literal}'https://tiles.wmflabs.org/hikebike/{z}/{x}/{y}.png';{/literal}
    // var osmUrl = {literal}'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';{/literal}
    let osmAttrib = 'Map data © <a href="https://www.openstreetmap.org">OpenStreetMap</a> contributors';
    let osm = new L.TileLayer(osmUrl, {
      minZoom: 0,
      // maxZoom: 12,
      attribution: osmAttrib
    });

    {if !isset($current_user) or is_null($current_user->home_latitude) or is_null($current_user->home_longitude)}
        let center = PARIS;
        let zoom = 3;
    {else}
        let center = new L.LatLng({$current_user->home_latitude}, {$current_user->home_longitude});
        let zoom = 10;
    {/if}

    // start the map
    map.setView(center, zoom);
    map.addLayer(osm);

    return map;
}

let geojsonMarkerOptions = {
    radius: 4,
    fillColor: "#ff7800",
    color: "#000",
    weight: 1,
    opacity: 1,
    fillOpacity: 0.8
};

function getColorDistance(x) {
    return  x < 500     ?    '#ffffb2':
            x < 1000    ?    '#fecc5c':
            x < 1500    ?    '#fd8d3c':
            x < 2000    ?    '#f03b20':
            '#bd0026' ;
};
function getColorCachesCount(x) {
    return  x < 10     ?    '#ffffb2':
            x < 50     ?    '#fecc5c':
            x < 100    ?    '#fd8d3c':
            x < 200    ?    '#f03b20':
            '#bd0026' ;
};
function getColorMovedDays(x) {
    return  x < 10     ?    '#2db100':
            x < 90     ?    '#68c742':
            x < 180    ?    '#e7f65f':
            x < 365    ?    '#fd8d3c':
            x < 730    ?    '#f03b20':
            '#84001a' ;
};
function pointToLayer(feature, latlng) {
    let marker = geojsonMarkerOptions;
    let showBy = $("input[name='show-by']:checked").val();
    if (showBy === 'distance') {
        marker.fillColor = getColorDistance(feature.properties.distance)
    } else if (showBy === 'caches') {
        marker.fillColor = getColorCachesCount(feature.properties.caches_count)
    } else {
        marker.fillColor = getColorMovedDays(feature.properties.days)
    }
    return L.circleMarker(latlng, geojsonMarkerOptions);
}

// ----------------------------------- JQUERY - MAP INIT - END
