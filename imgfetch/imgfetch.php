<?php

defined('ABSPATH') or die("No script kiddies please!");

/**
 * Plugin Name: image fetch
 * Description: fetch image by url
 * Version: 1.0.0
 * Author: Serpent7776
 * License: 2-clause BSD
 */

/*
 * Copyright © 2015 Serpent7776. All Rights Reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *	1. Redistributions of source code must retain the above copyright
 *	   notice, this list of conditions and the following disclaimer.
 *	2. Redistributions in binary form must reproduce the above copyright
 *	   notice, this list of conditions and the following disclaimer in the
 *	   documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

if (!is_admin()) {
	return;
}

class ImgFetch {

	private static $usercap = 'upload_files';

	static function admin_menu() {
		add_submenu_page('upload.php', 'Image fetch', 'Image fetch', self::$usercap, 'imgfetch-submenu', array(__CLASS__, 'imgfetch_handle_page'));
	}

	static function imgfetch_handle_page() {
		if (!current_user_can(self::$usercap)) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		if (!empty($_POST['image-url'])) {
			$image_url = $_POST['image-url'];
			if (filter_var($image_url, FILTER_VALIDATE_URL)) {
				list($image_html, $error_msg) = self::fetch_image($image_url);
			} else {
				$error_msg = 'invalid url';
			}
		}
		self::output_imgfetch_page(
			empty($image_url) ? '' : $image_url,
			empty($image_html) ? '' : $image_html,
			empty($error_msg) ? '' : $error_msg
		);
	}

	static function output_imgfetch_page($image_url = '', $image_html = '', $error_msg = '') {
		$error_html = empty($error_msg) ? '' : "<div class='error'>{$error_msg}</div>";
		echo <<<HTML
<div class="wrap">
	<h2>Image fetch</h2>
	$error_html
	<div class="media-toolbar wp-filter" style="padding: 10px;">
		<form action="" method="post">
			<label>
				<span>Image url</span>
				<input class="media-button" type="url" name="image-url" value="{$image_url}" required="required" />
				<button type="submit" class="button">Fetch</button>
			</label>
		</form>
	</div>
	<div>{$image_html}</div>
</div>
HTML;
	}

	static function fetch_image($image_url) {
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$post_id = 0;
		$r = media_sideload_image($image_url, $post_id);
		if ($r instanceof WP_Error) {
			$errors = array_map(function($item) {
				if (is_array($item)) {
					return implode('; ', array_values($item));
				} else {
					return $item;
				}
			}, $r->errors);
			$errors = implode('; ', $errors);
			return array('', $errors);
		} else {
			return array($r, '');
		}
	}

}

add_action('admin_menu', array('ImgFetch', 'admin_menu'));
