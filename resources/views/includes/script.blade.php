<script>
    mapboxgl.accessToken = '{{ config('mapbox.mapbox_token', null) }}';

    @if ($rtl ?? '')
        mapboxgl.setRTLTextPlugin(
            'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-rtl-text/v0.2.3/mapbox-gl-rtl-text.js',
            null,
            true
        );
    @endif

    const map = new mapboxgl.Map({
        container: '{{ $id }}',
        style: 'mapbox://styles/{{ $mapStyle }}',
        {{--center: [{{ $center['long'] ?? $center[0] }}, {{ $center['lat'] ?? $center[1] }}],--}}
        zoom: {{ $zoom }},
        interactive: {{ $interactive ? 'true' : 'false' }},
        cooperativeGestures: {{ $cooperativeGestures ? 'true' : 'false' }},
    });

    @if(isset($center['long']) || is_numeric($center[0]))
        map.flyTo({
            center: [{{ $center['long'] ?? $center[0] }}, {{ $center['lat'] ?? $center[1] }}]
        });
    @else
        map.fitBounds({{json_encode($center)}});
    @endif



    {{ $navigationControls ? 'map.addControl(new mapboxgl.NavigationControl());' : '' }}

    map.on('load', function() {
        map.resize();
    });


    @if ($draggable ?? '')
        let long = 0;
        let lat = 0;
        const marker = new mapboxgl.Marker({
                draggable: true
            })
            .setLngLat([0, 0])
            .addTo(map);
    @endif

    @foreach ($markers as $key => $marker)


        @if (isset($marker['icon']))
            const el{{ $key }} = document.createElement('div');
            el{{ $key }}.className = 'marker';

            @if (isset($marker['icon_active']))
                el{{ $key }}.innerHTML = `<span class="main-icon">{!! $marker['icon'] !!}</span>` + `<span class="active-icon" style="display:none;">{!! $marker['icon_active'] !!}</span>`
            @elseif (isset($marker['icon']))
                el{{ $key }}.innerHTML = `<span class="main-icon">{!! $marker['icon'] !!}</span>`;
            @endif

            new mapboxgl.Marker(el{{ $key }})
                .setLngLat([{{ $marker['long'] }}, {{ $marker['lat'] }}])
                .setPopup(new mapboxgl.Popup({
                    offset: 25
                })
                @isset($marker['description'])
                    .setText('{{ $marker['description'] }}')
                @endisset
                @isset($marker['html_description'])
                    .setHTML(`{!! $marker['html_description'] !!}`)
                @endisset
                    .on("close", resetIcons)
                @if (isset($marker['icon_active']))
                    .on("open", switchIcons)
                @endif
            ).addTo(map);
        @else
            new mapboxgl.Marker()
                .setLngLat([{{ $marker['long'] }}, {{ $marker['lat'] }}])
                .setPopup(new mapboxgl.Popup({
                    offset: 25
                })
                @isset($marker['description'])
                    .setText('{{ $marker['description'] }}')
                @endisset
                @isset($marker['html_description'])
                    .setHTML(`{!! $marker['html_description'] !!}`)
                @endisset
                @if (isset($marker['icon_active']))
                    .on("close", resetIcons)
                    .on("open", switchIcons)
                @endif
            ).addTo(map);
        @endif
    @endforeach

    function resetIcons(){
        const icons = document.querySelectorAll('.main-icon');

        icons.forEach(icon => {
          icon.style.display = 'block';
        });

        const active_icons = document.querySelectorAll('.active-icon');

        active_icons.forEach(a_icon => {
          a_icon.style.display = 'none';
        });
    }

    function switchIcons(e){
        e.target._marker._element.getElementsByClassName("main-icon")[0].style.display = 'none'
        e.target._marker._element.getElementsByClassName("active-icon")[0].style.display = "block"
    }
</script>
