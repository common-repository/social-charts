<?php
defined('ABSPATH') or die();
// Variable is needed for display settings
// $sc_settings 
?>

<div class="wrap">

  <?php
    if (isset($_GET['message'])) {
      echo '<div class="notice notice-success is-dismissible">';
      echo '<p>';
      echo __('Settings Saved Successfully', 'social-charts');
      echo '</p>';
      echo '</div>';
    }
  ?>

  <div class="metabox-holder">
    <div class="postbox sc-heading">
      <div>Social Charts - Settings</div>
    </div>
  </div>

  <div class="metabox-holder">
    <div class="postbox sc-postbox">
      <form class="sc-settings-form" method="post" action="<?php echo admin_url() . 'admin-post.php' ?>">

        <input type="hidden" name="action" value="social_charts_settings_action" />
        <h4>Instagram</h4>

        <div class="label-field-wrapper">
          <label><?php _e('Instagram username', 'social-charts'); ?></label>
          <div class="sc-option-field">
            <input type="text" name="social_profiles[instagram][username]" value="<?php echo esc_attr($sc_settings['social_profiles']['instagram']['username']); ?>" required />
          </div>
        </div>

        <div class="label-field-wrapper">
          <label><?php _e('Number of days to show backwards', 'social-charts'); ?></label>
          <div class="sc-option-field">
            <input min="5" type="number" name="social_profiles[instagram][days]" value="<?php echo esc_attr($sc_settings['social_profiles']['instagram']['days']); ?>" required />
          </div>
        </div>

        <div class="label-field-wrapper">
          <label><?php _e('Activated', 'social-charts'); ?></label>
          <div class="sc-option-field">
            <input type="checkbox" name="social_profiles[instagram][active]" value="<?php echo esc_attr($sc_settings['social_profiles']['instagram']['active']); ?>" <?php echo $sc_settings['social_profiles']['instagram']['active'] ? ' checked' : ''; ?> />
          </div>
        </div>

        <div style="margin-bottom: 30px; font-weight: bold;">
          <div>For showing the chart use the shortcode: [socialcharts_chart platform="instagram"]</div>
        </div>

        <div style="margin-bottom: 30px; font-weight: bold;">
          <div style="line-height: 1.5em;">
            Note: The follower count is updated once a day. So it will take some time until the chart is filled.<br>
            In the beginning it will show the current follower count backwards as the default value. This will be updated
            day after day.
          </div>
        </div>

        <input type="submit" class="button button-primary" value="Save changes" name="sc_settings_submit" />
      </form>
    </div>
  </div>

</div> <!-- wrap -->