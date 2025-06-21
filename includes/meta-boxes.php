<?php
// includes/meta-boxes.php

add_action('add_meta_boxes', function () {
    add_meta_box('program_details', 'Program Details', 'pf_render_fields', 'programs', 'normal', 'high');
});

function pf_render_fields($post) {
    $fields = [
        'location' => 'Location (City/State)',
        'overall_gpa' => 'Overall GPA',
        'tuition_fee' => 'Tuition Fee',
        'degree_name' => 'Degree Name',
        'website_link' => 'Website Link',
        'healthcare_experience' => 'Healthcare Experience (years)',
        'start_month' => 'Start Month (e.g., January)',
    ];

    foreach ($fields as $key => $label) {
        $value = get_post_meta($post->ID, $key, true);
        echo "<p><label><strong>{$label}</strong><br>
              <input type='text' name='{$key}' id='{$key}' value='" . esc_attr($value) . "' style='width:100%;'></label></p>";
    }

    // Leaflet Map Fields
    $lat = get_post_meta($post->ID, '_lat', true);
    $lng = get_post_meta($post->ID, '_lng', true);

    echo "<p><label><strong>Latitude</strong><br>
          <input type='text' id='program_lat' name='program_lat' value='" . esc_attr($lat) . "' style='width:100%;'></label></p>";

    echo "<p><label><strong>Longitude</strong><br>
          <input type='text' id='program_lng' name='program_lng' value='" . esc_attr($lng) . "' style='width:100%;'></label></p>";

    echo '<div id="leaflet-map" style="height: 400px;"></div>';

    // Inline JS
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const latInput = document.getElementById('program_lat');
        const lngInput = document.getElementById('program_lng');
        const locationInput = document.getElementById('location');

        let defaultLat = parseFloat(latInput.value) || 23.6850;
        let defaultLng = parseFloat(lngInput.value) || 90.3563;

        const map = L.map('leaflet-map').setView([defaultLat, defaultLng], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);

        marker.on('dragend', function (e) {
            const pos = marker.getLatLng();
            latInput.value = pos.lat.toFixed(6);
            lngInput.value = pos.lng.toFixed(6);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            latInput.value = e.latlng.lat.toFixed(6);
            lngInput.value = e.latlng.lng.toFixed(6);
        });

        locationInput.addEventListener('blur', function () {
            const query = locationInput.value;
            if (!query) return;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);

                        latInput.value = lat.toFixed(6);
                        lngInput.value = lon.toFixed(6);

                        marker.setLatLng([lat, lon]);
                        map.setView([lat, lon], 10);
                    } else {
                        alert("Location not found!");
                    }
                })
                .catch(() => alert("Location search failed!"));
        });
    });
    </script>
    <?php
}


// Save data
add_action('save_post', function ($post_id) {
    if (get_post_type($post_id) != 'programs') return;

    $keys = ['location', 'overall_gpa', 'tuition_fee', 'degree_name', 'website_link', 'healthcare_experience', 'start_month'];
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
        }
    }

    if (isset($_POST['program_lat'])) {
        update_post_meta($post_id, '_lat', sanitize_text_field($_POST['program_lat']));
    }
    if (isset($_POST['program_lng'])) {
        update_post_meta($post_id, '_lng', sanitize_text_field($_POST['program_lng']));
    }
});
