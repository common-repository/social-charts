<?php

/**
 * Fired during plugin activation
 *
 * @link       todo
 * @since      1.0.0
 *
 * @package    Social_Charts
 * @subpackage Social_Charts/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Social_Charts
 * @subpackage Social_Charts/includes
 * @author     Christian <todo>
 */
class Social_Charts_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
    
    // create tables
    $sc_db = new SocialChartsDB();
    $sc_db->install();

    // set default options
    $default_options = array(
      'social_profiles' => array(
        'instagram' => array(
          'username' => '',
          'days' => 14,
          'active' => true
        )
      )
    );
    update_option('sc_settings', $default_options);

    // $current_date = date('Y-m-d');
    // $media = 3;
    // $follows = 4;
    // $followed_by = 5;
    // $sc_db->insert_record_instagram($current_date, 'kilian.jonas', $media, $follows, $followed_by);

	}

}
