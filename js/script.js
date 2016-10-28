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

    marker.addListener('click', function() {
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
