var map;
var marker;

$(document).ready(function() {

    var lat = $('#location_map').attr('data-lat');
    var lng = $('#location_map').attr('data-lng')
    if(!lat) {
        lat = 51.500152;
    }
    if(!lng) {
        lat = -0.126236;
    }
    var mapOptions = {
        center : new google.maps.LatLng(lat, lng),
        zoom : 10,
        mapTypeId : google.maps.MapTypeId.ROADMAP
    };
    $('#location_map').attr('data-lat');
    map = new google.maps.Map(document.getElementById("location_map"), mapOptions);

    $('#location_query_search').click(function() {
        var res = locationSearch($('#location_query').val(), function(data) {
            console.log(data);
            if(data.length == 0) {
                html = '<div class="row-fluid item"><div class="span12"><img width="32" height="32"/><strong>No Results Found!</strong></div></div>';
            } else {
                for(var i = 0; i < data.length; i++) {
                    if(data[i].categories.length > 0) {
                        var icon = data[i].categories[0].icon;
                        icon = icon.prefix + '32' + icon.name;
                    } else {
                        icon = 'https://foursquare.com/img/categories/food/bagels_32.png';
                    }
                    var row = '<div class="row-fluid item" data-id="' + data[i].id + '" data-name="' + data[i].name + '" data-lat="' + data[i].location.lat + '" data-lng="' + data[i].location.lng + '" style="background-image:url('+icon+')">';
                    row += '<div class="span12"><strong>' + data[i].name + '</strong></div>';
                    row += '</div>';
                    html = row;

                }
            }
            $('#location_results').html(html);
            $('#location_results .item').click(function() {
                $('#location_query').val($(this).attr('data-name'));
                $('#location_id').val($(this).attr('data-id'));
                loc = new google.maps.LatLng($(this).attr('data-lat'), $(this).attr('data-lng'));
                placeMarker(loc, $(this).attr('data-name'));
                $('#location_results').hide();
            })
            $('#location_results').show();
        });
        return false;
    })
});

function placeMarker(loc, title){
   marker = new google.maps.Marker({
            position: loc,
            map: map,
            title: title
        });
}

function locationSearch(query, callback) {
    var url = getURL('/location/search/') + '~/' + query + '/' + '5';
    console.log(url);
    $.ajax({
        url : url,
        dataType : "json"
    }).done(callback);
}