function processPoint(point) {
    var marker = new google.maps.Marker({
        position: {lat: point[1], lng: point[2]},
        icon: {
            url: Map.types[point[3]].icon,
            scaledSize: new google.maps.Size(Map.types[point[3]].size[0], Map.types[point[3]].size[1]),
            origin: new google.maps.Point(Map.types[point[3]].origin[0], Map.types[point[3]].origin[1]),
            anchor: new google.maps.Point(Map.types[point[3]].anchor[0], Map.types[point[3]].anchor[1])
        },
        title: Map.sources[point[0]].label
    });

    marker.type = point[3];

    marker.searchData = $.friendly_id(Map.sources[point[0]].label); // Source title
    marker.searchData += ' ' + $.friendly_id(Map.types[point[3]].label); // Type
    marker.searchData += ' ' + $.friendly_id(point[4]); // Date
    for (var key in point[5]) {
        marker.searchData += ' ' + $.friendly_id(point[5][key]); // Extra data
    }

    marker.addListener('click', function () {
        var content = $('<div/>');

        var source;
        if (typeof Map.sources[point[0]].logo !== 'undefined' && Map.sources[point[0]].logo)
            source = $('<img/>', {src: Map.sources[point[0]].logo}).css('height', '50px');
        else
            source = $('<strong/>').text(Map.sources[point[0]].label);
        if (typeof Map.sources[point[0]].link !== 'undefined' && Map.sources[point[0]].link)
            source = source.wrap($('<a/>', {href: Map.sources[point[0]].link, target: '_blank'})).parent();

        content.append(source);

        content.append($('<strong/>').text(point[4]).css({display: 'block', color: '#999999'}));

        var extra = $('<p/>');
        var hasExtra = false;

        for (var key in point[5]) {
            if (hasExtra)
                extra.append($('<br/>'));
            extra.append($('<span/>').text(point[5][key]).prepend($('<strong/>').text(key + ': ')));
            hasExtra = true;
        }

        if (hasExtra)
            content.append(extra);

        Map.infoWindow.setContent(content.html());
        Map.infoWindow.open(Map.map, marker);
    });

    return marker;
}

function toggleLegend() {
    var legend = document.getElementById('legend');

    if (!legend.hasAttribute('data-content')) {
        legend.setAttribute('data-content', legend.innerHTML);
    }

    if (legend.className == 'collapsed') {
        legend.className = '';
        legend.innerHTML = legend.getAttribute('data-content');
        updateLegendCounts();
    } else {
        legend.className = 'collapsed';
        legend.innerHTML = '?';
    }
}

function updateLegendCounts() {
    var legend = $('#legend');
    if (legend.hasClass('collapsed'))
        return;

    var counts = {};
    window.markerClusterer.getMarkers().map(function(marker) {
        if (typeof counts[marker.type] === 'undefined')
            counts[marker.type] = 0;
        counts[marker.type]++;
    });

    $('li[data-type]', legend).each(function() {
        var type = $(this).attr('data-type');
        $('i', this).text('(' + (typeof counts[type] === 'undefined' ? 0 : counts[type]) + ')');
    });
}

function doSearch() {
    var query = $.friendly_id(document.getElementById('search').value.trim());

    var filteredMarkers = query ? window.allMarkers.filter(function (marker) {
        return marker.searchData.indexOf(query) !== -1;
    }) : window.allMarkers;

    if ($('#search').hasClass('empty') && filteredMarkers.length > 0)
        $('#search').removeClass('empty');
    else if (!$('#search').hasClass('empty') && filteredMarkers.length === 0)
        $('#search').addClass('empty');

    window.markerClusterer.clearMarkers();
    window.markerClusterer.addMarkers(filteredMarkers);
    updateLegendCounts();
}

var searchThrottle;
function searchChanged() {
    if (searchThrottle)
        clearTimeout(searchThrottle);

    searchThrottle = setTimeout(doSearch, 300);
}
