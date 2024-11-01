<?php

class SocialChartsDB {

  private $table_name_platforms = 'sc_platforms';
  private $table_name_data = 'sc_data';

  public function __construct()
  {
    global $wpdb;
    $this->table_name_platforms = $wpdb->prefix . $this->table_name_platforms;
    $this->table_name_data = $wpdb->prefix . $this->table_name_data;
  }

  public function install() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$this->table_name_platforms} (
      id mediumint NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      PRIMARY KEY  (id),
      CONSTRAINT tb_uq UNIQUE KEY (name)
    ) $charset_collate;";
  
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $wpdb->insert( 
      $this->table_name_platforms,
      array( 
        'name' => 'instagram'
      )
    );

    $sql = "CREATE TABLE {$this->table_name_data} (
      id mediumint NOT NULL AUTO_INCREMENT,
      platform_id mediumint NOT NULL,
      date DATE NOT NULL,
      media INT DEFAULT NULL,
      follows INT DEFAULT NULL,
      followed_by INT DEFAULT NULL,
      username varchar(255) NOT NULL,
      PRIMARY KEY  (id),
      FOREIGN KEY (platform_id) REFERENCES {$this->table_name_platforms}(id)
    ) $charset_collate;";

    dbDelta($sql);

  }

  public function fill_data_backwards($arg_days_backwards, $arg_username) {
    global $wpdb;
    $sql_min = "SELECT * FROM {$this->table_name_data} WHERE (username = %s) AND (date = (SELECT MIN(date) FROM {$this->table_name_data} WHERE username = %s))";
    $sql_min_prepared = $wpdb->prepare($sql_min, $arg_username, $arg_username);

    $data_min = $wpdb->get_row($sql_min_prepared);
    if ($data_min == null) {
      return;
    }

    $date_min_available = new DateTime($data_min->date);
    $today = new DateTime('today');
    $date_required = $today->sub(new DateInterval("P{$arg_days_backwards}D"));

    if ($date_required >= $date_min_available) {
      return; // data is available, everything is fine
    }

    // loop through missing days
    while ($date_min_available >= $date_required) {
      if (!$this->is_date_available($date_min_available->format('Y-m-d'), $arg_username)) {
        $wpdb->insert( 
          $this->table_name_data,
          array( 
            'platform_id' => 1,
            'date' => $date_min_available->format('Y-m-d'),
            'media' => $data_min->media,
            'follows' => $data_min->follows,
            'followed_by' => $data_min->followed_by,
            'username' => $arg_username
          )
        );
      }

      $date_min_available->sub(new DateInterval('P1D'));

    }

  }

  public function is_date_available($arg_date, $arg_username) {
    global $wpdb;
    // check if entry for date already exists
    $sql = "SELECT * FROM {$this->table_name_data} WHERE date = %s AND username = %s";
    $sql_prepared = $wpdb->prepare($sql, $arg_date, $arg_username);
    $data_row = $wpdb->get_row($sql_prepared);
    if ($data_row !== null) {
      return true; // there are already data for this date in the database, do not continue
    }
    return false;
  }


  public function insert_record_instagram($arg_date, $arg_username, $arg_media, $arg_follows, $arg_followed_by) {
    
    global $wpdb;

    // check if entry for date already exists
    if ($this->is_date_available($arg_date, $arg_username)) {
      return; // there are already data for this date in the database, do not continue
    }

    $sql = "INSERT INTO {$this->table_name_data}(platform_id, date, media, follows, followed_by, username)
      VALUES ((SELECT id FROM {$this->table_name_platforms} WHERE name = 'instagram'), %s, %d, %d, %d, %s)";
    $sql_prepared = $wpdb->prepare($sql, $arg_date, $arg_media, $arg_follows, $arg_followed_by, $arg_username);
    $wpdb->query($sql_prepared);

  }

  public function get_data($arg_username, $arg_days_back) {

    global $wpdb;
    $sql = "SELECT followed_by, date FROM {$this->table_name_data} 
            WHERE (platform_id = (SELECT id FROM {$this->table_name_platforms} WHERE name = 'instagram')) 
            AND (username = %s)
            ORDER BY date DESC LIMIT %d";
    $sql_prepared = $wpdb->prepare($sql, $arg_username, $arg_days_back);
    $followed_by_data = $wpdb->get_results($sql_prepared);
    return $followed_by_data;

  }

}
