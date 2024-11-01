<?php

/**
 * Plugin Name:       Social Charts
 * Description:       Monitor and show your social media follower development as a beautiful chart
 * Version:           1.0.0
 * Author:            protectyouruploads
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       social-charts
 * Domain Path:       /languages 
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('SOCIAL_CHARTS_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-social-charts-activator.php
 */
function activate_social_charts()
{
  require_once plugin_dir_path(__FILE__) . 'includes/class-social-charts-activator.php';
  require_once plugin_dir_path(__FILE__) . 'includes/class-social-charts-db.php';
  Social_Charts_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-social-charts-deactivator.php
 */
function deactivate_social_charts()
{
  require_once plugin_dir_path(__FILE__) . 'includes/class-social-charts-deactivator.php';
  Social_Charts_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_social_charts');
register_deactivation_hook(__FILE__, 'deactivate_social_charts');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-social-charts.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_social_charts()
{

  $plugin = new Social_Charts();
  $plugin->run();
}


add_action('social_charts_cron_hook', 'social_charts_cron_exec');

if (!wp_next_scheduled('social_charts_cron_hook')) {
  wp_schedule_event(time(), 'daily', 'social_charts_cron_hook');
}


if (!function_exists('social_charts_update_database')) {
  function social_charts_update_database()
  {
    require_once('includes/class-social-charts-db.php');
    require_once('includes/class-social-charts-instagram-api.php');
    $sc_settings = get_option('sc_settings');
    if ($sc_settings) {
      if ($sc_settings['social_profiles']['instagram']['active'] && $sc_settings['social_profiles']['instagram']['username']) {
        $sc_db = new SocialChartsDB();
        $ig_api = new SocialChartsInstagramAPI($sc_settings['social_profiles']['instagram']['username']);

        $current_date = date('Y-m-d');
        $sc_db->insert_record_instagram(
          $current_date,
          $sc_settings['social_profiles']['instagram']['username'],
          $ig_api->get_media_count(),
          $ig_api->get_follows(),
          $ig_api->get_follower_count()
        );
        $sc_db->fill_data_backwards($sc_settings['social_profiles']['instagram']['days'], $sc_settings['social_profiles']['instagram']['username']);
      }
    }
  }
}


function social_charts_cron_exec()
{
  sc_update_database();
}


run_social_charts();

if (!function_exists('social_charts_settings')) {
  function social_charts_settings()
  {
    $sc_settings = get_option('sc_settings');
    include('social-charts-settings.php');
  }
}

if (!function_exists('social_charts_add_menu')) {
  function social_charts_add_menu()
  {
    add_menu_page(__('Social Charts', 'social-charts'), __('Social Charts', 'social-charts'), 'manage_options', 'main-ap-social-charts', 'social_charts_settings', 'dashicons-chart-area');
  }
}

add_action('admin_menu', 'social_charts_add_menu'); //adds plugin menu in wp-admin


add_action('admin_post_social_charts_settings_action', 'social_charts_settings_action'); //recieves the posted values from settings form

if (!function_exists('social_charts_settings_action')) {
  function social_charts_settings_action()
  {
    if (!empty($_POST)) {
      $sc_settings = array();
      $sc_settings['social_profiles'] = array();
      $sc_settings['social_profiles']['instagram']['username'] = sanitize_text_field(rtrim($_POST['social_profiles']['instagram']['username']));
      $sc_settings['social_profiles']['instagram']['days'] = sanitize_text_field($_POST['social_profiles']['instagram']['days']);
      if (isset($_POST['social_profiles']['instagram']['active'])) $sc_settings['social_profiles']['instagram']['active'] = true;
      else $sc_settings['social_profiles']['instagram']['active'] = false;

      update_option('sc_settings', $sc_settings);
      social_charts_update_database();
      wp_redirect(admin_url() . 'admin.php?page=main-ap-social-charts&message=1');
    }
  }
}



if (!function_exists('shortcode_socialcharts')) {
  // [socialcharts_chart platform="instagram"]
  function shortcode_socialcharts($atts)
  {
    $a = shortcode_atts(array(
      'platform' => 'instagram'
    ), $atts);

    if ($a['platform'] != 'instagram') {
      return "<div>Platform {$a['platform']} is not suppported.</div>";
    }

    require_once('includes/class-social-charts-db.php');

    $sc_db = new SocialChartsDB();
    $sc_settings = get_option('sc_settings');
    if (!$sc_settings) {
      return "<p></p>";
    }

    wp_enqueue_script('chartjs', plugin_dir_url(__FILE__) . 'chartjs/Chart.bundle.min.js');
    wp_enqueue_script('sc-charts-instagram', plugin_dir_url(__FILE__) . 'public/js/social-charts-instagram.js', array('chartjs'), '1.0.0');

    $data = $sc_db->get_data($sc_settings['social_profiles']['instagram']['username'], $sc_settings['social_profiles']['instagram']['days']);

    $js_data = array();
    // put together data variable
    foreach ($data as $record) {
      $js_data[] = array(
        't' => $record->date,
        'y' => (int) $record->followed_by
      );
    }

    $chart_data = array(
      'username' => $sc_settings['social_profiles']['instagram']['username'],
      'data' => $js_data
    );

    wp_localize_script('sc-charts-instagram', 'sc_chart_data', $chart_data);
    return "<p><canvas id='socialChartInstagram'></canvas></p>";
  }
}


add_shortcode('socialcharts_chart', 'shortcode_socialcharts');
