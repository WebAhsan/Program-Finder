<?php
/**
 * Plugin Name: Program Finder
 * Description: Add university programs and show them on map with filters.
 * Version: 1.0
 * Author: Ashikul Ahsan
 */

defined('ABSPATH') or die('No script kiddies please!');

define('PF_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Load files
include PF_PLUGIN_DIR . 'includes/post-type.php';
include PF_PLUGIN_DIR . 'includes/meta-boxes.php';
include PF_PLUGIN_DIR . 'includes/shortcode.php';

// Enqueue assets
add_action('wp_enqueue_scripts', function () {
    
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet/dist/leaflet.css');
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');

    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet/dist/leaflet.js', [], null, true);
    wp_enqueue_script('pf-script', plugins_url('assets/script.js', __FILE__), ['jquery', 'leaflet-js'], null, true);
    wp_enqueue_style('pf-style', plugins_url('assets/style.css', __FILE__));
    
});

// For admin
add_action('admin_enqueue_scripts', function ($hook) {
    global $post;
    if ($hook === 'post-new.php' || $hook === 'post.php') {
        if ($post && $post->post_type === 'programs') {
            wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
            wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], null, true);
        }
    }
});




add_action('wp_ajax_filter_programs', 'pf_filter_programs');
add_action('wp_ajax_nopriv_filter_programs', 'pf_filter_programs');

function pf_filter_programs() {
    $state = sanitize_text_field($_POST['state']);
    $degree = sanitize_text_field($_POST['degree']);
    $experience = sanitize_text_field($_POST['experience']);
    $gpa = floatval($_POST['gpa']);
    $start_month = sanitize_text_field($_POST['start_month']);

    $args = [
        'post_type' => 'programs',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => ['relation' => 'AND']
    ];

    if (!empty($state)) {
        $args['meta_query'][] = [
            'key' => 'location',
            'value' => $state,
            'compare' => '='
        ];
    }

    if (!empty($degree)) {
        $args['meta_query'][] = [
            'key' => 'degree_name',
            'value' => $degree,
            'compare' => '='
        ];
    }

    if (!empty($experience)) {
        $args['meta_query'][] = [
            'key' => 'healthcare_experience',
            'value' => $experience,
            'type' => 'NUMERIC',  // Add this
            'compare' => '='
        ];
    }

    if (!empty($gpa)) {
        $args['meta_query'][] = [
            'key' => 'overall_gpa',
            'value' => $gpa,
            'type' => 'NUMERIC',  // Add this
            'compare' => '='
        ];
    }

    if (!empty($start_month)) {
        $args['meta_query'][] = [
            'key' => 'start_month',
            'value' => $start_month,
            'compare' => '='
        ];
    }

    $query = new WP_Query($args);
    $html = '';
    $locations = [];

    if ($query->have_posts()) {
        $html .= '<div class="row">';
        while ($query->have_posts()) {
            $query->the_post();
            $title = get_the_title();
            $location = get_post_meta(get_the_ID(), 'location', true);
            $lat = get_post_meta(get_the_ID(), '_lat', true);
            $lng = get_post_meta(get_the_ID(), '_lng', true);
            $degree = get_post_meta(get_the_ID(), 'degree_name', true);
            $gpa_value = get_post_meta(get_the_ID(), 'gpa', true);
            $fee = get_post_meta(get_the_ID(), 'tuition_fee', true);
            $website = get_post_meta(get_the_ID(), 'website_link', true);
            $image = get_the_post_thumbnail_url(get_the_ID(), 'medium');

            $html .= '<div class="col-md-4 mb-4">';
            $html .= '<div class="card h-100 border-0 shadow-lg rounded-4" style="background: linear-gradient(135deg, #f8f9fa, #ffffff); font-family: Poppins, sans-serif;">';
            if ($image) {
                $html .= '<img src="' . esc_url($image) . '" alt="' . esc_attr($title) . '" class="card-img-top rounded-top-4" style="object-fit: cover; height: 200px;">';
            }
            $html .= '  <div class="card-body p-4" style="line-height:1.6;">';
            
            $html .= '    <h4 class="card-title mb-3" style="color: #800000;">' . esc_html($title) . '</h4>';
            
            $html .= '    <p class="mb-2"><span class="badge text-light" style="background-color: #800000;"><i class="bi bi-geo-alt-fill me-1"></i>' . esc_html($location) . '</span></p>';
            
            $html .= '    <div class="row mt-3 g-2">';
            $html .= '      <div class="col-6">';
            $html .= '        <div class="p-2 bg-light rounded shadow-sm">';
            $html .= '          <small class="text-muted">Degree</small><br>';
            $html .= '          <span style="color: #333;">' . esc_html($degree) . '</span>';
            $html .= '        </div>';
            $html .= '      </div>';
            
            $html .= '      <div class="col-6">';
            $html .= '        <div class="p-2 bg-light rounded shadow-sm">';
            $html .= '          <small class="text-muted">GPA</small><br>';
            $html .= '          <span style="color: #333;">' . esc_html($gpa_value) . '</span>';
            $html .= '        </div>';
            $html .= '      </div>';
            
            $html .= '      <div class="col-12">';
            $html .= '        <div class="p-2 bg-light rounded shadow-sm mt-2">';
            $html .= '          <small class="text-muted">Tuition Fee</small><br>';
            $html .= '          <span style="color: #28a745;">' . esc_html($fee) . '</span>';
            $html .= '        </div>';
            $html .= '      </div>';
            $html .= '    </div>';
            
            $html .= '    <a href="' . esc_url($website) . '" target="_blank" class="btn w-100 mt-4 text-white" style="background-color: #800000;">';
            $html .= '      🌐 Visit Website';
            $html .= '    </a>';
            
            $html .= '  </div>';
            $html .= '</div>';
            $html .= '</div>';




            if (!empty($lat) && !empty($lng)) {
                $locations[] = [
                    'title' => $title,
                    'location' => $location,
                    'lat' => $lat,
                    'lng' => $lng,
                    'title'    => get_the_title(),
                    'degree'   => $degree,
                    'gpa'      => $gpa_value,
                    'fee'      => $fee,
                    'website'  => $website,
                    'image'    => $image,

                ];
            }
        }
        $html .= '</div>';
    } else {
        $html = '<div class="alert alert-warning">No programs found matching your criteria.</div>';
    }
    


    wp_send_json([
        'html' => $html,
        'locations' => $locations
    ]);
    
    wp_die();
}
