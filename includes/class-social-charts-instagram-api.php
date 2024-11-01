<?php


class SocialChartsInstagramAPI {

  private $page_content;
  public $username;
  public $follower_count;
  public $follows;
  public $media_count;

  public function __construct($username)
  {
    $this->username = $username;
    $this->make_request();
  }

  public function make_request() {
    $url = "https://www.instagram.com/{$this->username}/";
    $response = wp_remote_get( "https://www.instagram.com/{$this->username}/");
    $body     =  wp_remote_retrieve_body( $response );
    // $get_return = file_get_contents($url);
    $this->page_content = $body;
  }

  public function get_follower_count() {
    $pattern = '/"edge_followed_by":{"count":([0-9]+)}/';
    $matches = array();
    preg_match($pattern, $this->page_content, $matches);
    // print_r($matches);
    $follower_count = (int) $matches[1];
    $this->follower_count = $follower_count;
    return $follower_count;
  }

  public function get_follows() {
    $pattern = '/"edge_follow":{"count":([0-9]+)}/';
    $matches = array();
    preg_match($pattern, $this->page_content, $matches);
    // print_r($matches);
    $follow_count = (int) $matches[1];
    $this->follows = $follow_count;
    return $follow_count;
  }

  public function get_media_count() {
    $pattern = '/"edge_owner_to_timeline_media":{"count":([0-9]+)/';
    $matches = array();
    preg_match($pattern, $this->page_content, $matches);
    // print_r($matches);
    $media_count = (int) $matches[1];
    $this->media_count = $media_count;
    return $media_count;
  }

// followed by
// "edge_followed_by":{"count":([0-9]+)}

// follows
// "edge_follow":{"count":([0-9]+)}

// media count
// "edge_owner_to_timeline_media":{"count":([0-9]+)

}