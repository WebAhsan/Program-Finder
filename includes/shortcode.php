<?php
add_shortcode('program_finder', 'pf_program_finder_shortcode');

function pf_program_finder_shortcode() {
    
    wp_enqueue_style( 'pf-style' );
    wp_enqueue_script( 'pf-script' );
    
    // Get all program posts
    $posts = get_posts([
        'post_type' => 'programs',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);

    // Collect unique meta values
    $states = $degrees = $experiences = $months = [];
    

    foreach ($posts as $post) {
        $states[] = get_post_meta($post->ID, 'location', true);
        $degrees[] = get_post_meta($post->ID, 'degree_name', true);
        $experiences[] = get_post_meta($post->ID, 'healthcare_experience', true);
        $months[] = get_post_meta($post->ID, 'start_month', true);
    }

    // Unique values only
    $states = array_unique(array_filter($states));
    $degrees = array_unique(array_filter($degrees));
    $experiences = array_unique(array_filter($experiences));
    $months = array_unique(array_filter($months));

    ob_start();
    ?>
  <div class="container my-5" id="pf-finder">
    <div class="card p-4 shadow-sm border-0" style="border-left: 5px solid #800000;">
        <h4 class="mb-4" style="color: #800000; font-weight: 600;">🔎 Search Programs</h4>

        <form id="pf-filter-form" class="row g-3">
    <div class="col-md-3">
        <select name="state" class="form-select">
            <option value="">Select State</option>
            <?php foreach ($states as $state): ?>
                <option value="<?= esc_attr($state); ?>"><?= esc_html($state); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="degree" class="form-select">
            <option value="">Select Degree</option>
            <?php foreach ($degrees as $degree): ?>
                <option value="<?= esc_attr($degree); ?>"><?= esc_html($degree); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="experience" class="form-select">
            <option value="">Healthcare Experience</option>
            <?php foreach ($experiences as $exp): ?>
                <option value="<?= esc_attr($exp); ?>"><?= esc_html($exp); ?> years</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <!-- For GPA, keep the input only, remove the disabled select -->
        <input type="number" step="0.01" name="gpa" class="form-control" placeholder="Minimum GPA">
    </div>
    <div class="col-md-3">
        <select name="start_month" class="form-select">
            <option value="">Start Month</option>
            <?php foreach ($months as $month): ?>
                <option value="<?= esc_attr($month); ?>"><?= esc_html($month); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3 d-grid align-self-end">
        <button type="submit" class="btn btn-primary">Search</button>
    </div>
</form>

    </div>
        <div class="mt-4 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary active" id="pf-toggle-map">
            🗺️ Map View
        </button>
        <button type="button" class="btn btn-outline-secondary" id="pf-toggle-grid">
            🧾 Grid View
        </button>
    </div>
    



    <div class="mt-4" id="pf-map-container">
        <div id="pf-map" style="height: 400px;" class="border rounded"></div>
    </div>
    
    <div class="mt-4 d-none" id="pf-results-container">
        <div id="pf-results"></div>
    </div>
    </div>
    
        
    <div id="pf-fullscreen-loader">
    <div class="pf-loader-spinner">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    </div>
    
    

    <script>
    

    

   const ajaxurl = "<?= admin_url('admin-ajax.php'); ?>";

    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("pf-filter-form");
    
        // Initialize map centered on USA
        let map = L.map('pf-map').setView([37.0902, -95.7129], 4);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
    
        let markers = [];
    
        function updateMap(locations) {
            // Clear old markers
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
        
            // Add new markers
            locations.forEach(loc => {
                if (loc.lat && loc.lng) {
                    const lat = parseFloat(loc.lat);
                    const lng = parseFloat(loc.lng);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const marker = L.marker([lat, lng])
                            .addTo(map)
                            .bindPopup(`
                                <div style="
                                    font-family: 'Poppins', sans-serif;
                                    max-width: 250px;
                                    border-radius: 12px;
                                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                                    overflow: hidden;
                                    background: #fff;
                                ">
                                    <img src="${loc.image}" alt="${loc.title}" style="width: 100%; height: auto; display: block; border-bottom: 1px solid #eee;">
                                    <div style="padding: 10px;">
                                        <h5 style="margin: 0 0 5px; color: #800000; font-weight: 600;">${loc.title}</h5>
                                        <p style="margin: 0 0 5px; font-size: 13px;"><i class="bi bi-geo-alt-fill"></i> ${loc.location}</p>
                                        <p style="margin: 0; font-size: 13px;"><strong>Degree:</strong> ${loc.degree}</p>
                                        <p style="margin: 0; font-size: 13px;"><strong>GPA:</strong> ${loc.gpa}</p>
                                        <p style="margin: 0 0 5px; font-size: 13px;"><strong>Fee:</strong> $${loc.fee}</p>
                                        <a href="${loc.website}" target="_blank" style="
                                            display: inline-block;
                                            margin-top: 8px;
                                            padding: 5px 10px;
                                            background: #800000;
                                            color: white;
                                            text-decoration: none;
                                            border-radius: 5px;
                                            font-size: 13px;
                                            font-weight: 500;
                                        ">🌐 Visit Website</a>
                                    </div>
                                </div>
                            `);
                        markers.push(marker);
                    }

                }
            });
        
            // Fit map to markers bounds if any
            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.2));
            }
        }

    
        const loaderOverlay = document.getElementById("pf-fullscreen-loader");

        form.addEventListener("submit", function (e) {
            e.preventDefault();
        
            const formData = new FormData(form);
            
            loaderOverlay.style.display = "block"; // Show overlay
        
            fetch(ajaxurl + "?action=filter_programs", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loaderOverlay.style.display = "none"; // Hide overlay
                document.getElementById("pf-results").innerHTML = data.html;
                updateMap(data.locations);
            })
            .catch(error => {
                console.error('AJAX error:', error);
                loaderOverlay.style.display = "none"; // Hide on error too
            });
        });


    });

    </script>
  
    <?php
    return ob_get_clean();
}
