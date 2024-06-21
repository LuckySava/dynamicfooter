<?php
class FooterContentFetcher {
	private $base_url;
	private $transient_key = 'footer_content_transient_v1.4';
//	private $cache_expiration = WEEK_IN_SECONDS; // Cache expiration time (1 week)
	private $cache_expiration = 60; // Cache expiration time (1 week)

	public function __construct($base_url) {
		$this->base_url = $base_url;
	}

	public function get_footer_content($path = '/') {
		$url = $this->base_url;
		if ($path !== '/') {
			$url .= '?url=' . urlencode($path);
		}

		// Log the constructed URL
		error_log('Constructed URL: ' . $url);

		// Generate a unique transient key based on the URL
		$transient_key = $this->transient_key . md5($url);

		// Try to get the content from the transient cache
		$cached_content = get_transient($transient_key);

		if ($cached_content !== false) {
			// Return the cached content if it exists and is not expired
			return $cached_content;
		}

		$response = wp_remote_get($url);

		if (is_wp_error($response)) {
			error_log('HTTP request error: ' . $response->get_error_message());
			return '';
		}

		$body = wp_remote_retrieve_body($response);

		error_log('Response body: ' . $body);

		$data = json_decode($body, true); // Decode the JSON response

		// Debugging: Check if JSON decoding was successful and log any errors
		if (json_last_error() !== JSON_ERROR_NONE) {
			error_log('JSON decode error: ' . json_last_error_msg());
		}

		// Check if JSON contains the footer key
		if (isset($data['footer'])) {
			$footer_content = $data['footer'];
			// Save the content to the transient cache
			set_transient($transient_key, $footer_content, $this->cache_expiration);
			return $footer_content;
		}

		error_log('Footer key not found in JSON response'); // Debugging: Log if 'footer' key is not set

		return '';
	}
}
