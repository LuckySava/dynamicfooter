<?php
class FooterContentFetcher {
	private $base_url;
	private $transient_key = 'footer_content_transient_v1.1';
//	private $cache_expiration = WEEK_IN_SECONDS; // Cache expiration time (1 week)
	private $cache_expiration = 30; // Cache expiration time (1 min)

	public function __construct($base_url) {
		$this->base_url = $base_url;
	}

	public function get_footer_content($path = '/') {
		$url = $this->base_url . urlencode($path);
		$transient_key = $this->transient_key . md5($url);
		$cached_content = get_transient($transient_key);

		if ($cached_content !== false) {
			return $cached_content;
		}

		$response = wp_remote_get($url);

		// Check if the response is valid
		if (is_wp_error($response)) {
			return ''; // Return empty string if there was an error
		}

		// Get the body of the response
		$body = wp_remote_retrieve_body($response);

		// Decode the JSON response
		$data = json_decode($body, true);

		// Check if JSON decoding was successful and contains the footer key
		if (json_last_error() === JSON_ERROR_NONE && isset($data['footer'])) {
			$footer_content = $data['footer'];
			// Save the content to the transient cache
			set_transient($transient_key, $footer_content, $this->cache_expiration);
			return $footer_content;
		}

		return '';
	}
}
