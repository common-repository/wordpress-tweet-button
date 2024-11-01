<?php
/*
 Plugin Name: Tweet Button
 Plugin URI: http://mohanjith.com/wordpress/tweet-button.html
 Description: Add the official tweet button to your WordPress blog to let people share content on Twitter without having to leave the page.
 Author: S H Mohanjith
 Version: 1.0.2
 Author URI: http://mohanjith.com/
 Text Domain: tweet-button
 Stable tag: 1.0.2
 License: GPL

 Copyright 2010  S H Mohanjith (email : moha@mohanjith.net)
 */

global $tweetbutton;

class TweetButton {
	private static $count_orientations = array('none', 'horizontal', 'vertical');
	private static $translation_domain = 'tweet-button';

	// WordPress hooks
	public function TweetButton() {
		add_option('tweet_button_position', 'before');
		add_option('tweet_button_count', 'horizontal');
		add_option('tweet_button_lang', 'en');
		add_option('tweet_button_via', '');
		add_option('tweet_button_related_follow_author', 'yes');
		add_option('tweet_button_related', 'mohanjith:S H Mohanjith');
		add_option('tweet_button_position', 'prepend');
		add_option('tweet_button_css', 'float: left;');
		add_option('tweet_button_hashtags', 'yes');

		add_action('wp_footer', array(&$this, 'wp_footer'), 1000 );
		
		add_filter('the_content', array(&$this, 'the_content'), 8 );
		add_filter('admin_menu', array(&$this, 'admin_menu'));
		
		if (get_option('tweet_button_related_follow_author', 'yes') == 'yes') {
			add_filter('user_contactmethods', array(&$this, 'user_contactmethods'));
		}

		load_plugin_textdomain(self::$translation_domain, PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)), dirname(plugin_basename(__FILE__)));
	}
	
	public function admin_menu() {
		add_options_page('Tweet Button Plugin Options', 'Tweet Button', 8, __FILE__, array(&$this, 'plugin_options'));
	}
	
	public function user_contactmethods($methods) {		
		$methods['twitter'] = 'Twitter';
		return $methods;
	}
	
	public function wp_footer($content) {
		?>
		<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
		<?php
	}
	
	public function the_content($content) {
		global $post;
		
		$options = array();
		$related = array();
		
		if (get_option('tweet_button_related', 'mohanjith:S H Mohanjith')) {
			$related[] = get_option('tweet_button_related', 'mohanjith:S H Mohanjith');
		}
		
		if (get_option('tweet_button_related_follow_author', 'yes') == 'yes') {
			if (get_usermeta($post->post_author, 'twitter')) {
				$related[] = get_usermeta($post->post_author, 'twitter').":".get_the_author();
			}
		}
		
		$options['via'] = get_option('tweet_button_via', '');
		$options['count'] = get_option('tweet_button_count', 'horizontal');
		$options['related'] = join(',', $related);
		$options['lang'] = get_option('tweet_button_lang', 'en');
		$options['url'] = get_permalink();
		$options['text'] = get_the_title();
		
		$option_str = "";
		$url_o = array();
		foreach ($options as $k=>$v) {
			$url_o[] = "{$k}=".rawurlencode($v);
			$option_str .= " data-{$k}=\"{$v}\"";
		}
		$url_str = "?".join("&", $url_o);
		
		$button = '<span style="'.get_option('tweet_button_css', 'float: right;').'" ><a class="twitter-share-button" '.$option_str.' href="http://twitter.com/share'.$url_str.'" >Tweet</a></span>';
		
		if (get_option('tweet_button_position', 'prepend') == 'prepend') {
			return $button.$content;
		} else {
			return $content.$button;
		}
	}

	public function plugin_options() {
		?>
<div class="wrap">
<h2><?php _e('Tweet Button', self::$translation_domain); ?></h2>
<form method="post" action="options.php"><?php wp_nonce_field('update-options'); ?>
<iframe src="https://secure.mohanjith.com/wp/tweet-button.php"
	style="float: right; width: 187px; height: 240px;"></iframe>

<h3><?php _e('Options', self::$translation_domain) ?>:</h3>
<table>
	<tr valign="top">
		<th align="left"><label for="tweet_button_via"><?php _e('Twitter username (via)', self::$translation_domain); ?>:</label></th>
		<td><input type="text" id="tweet_button_via" name="tweet_button_via" value="<?php echo get_option('tweet_button_via'); ?>" /></td>
	</tr>
</table>

<table>
	<tr valign="top">
		<th align="left"><?php _e('Suggest to follow the post author', self::$translation_domain); ?>:</th>
		<td><select id="tweet_button_related_follow_author" name="tweet_button_related_follow_author">
			<option value="yes" <?php echo ("yes" == get_option('tweet_button_related_follow_author', 'yes'))?'checked="checked"':''; ?> ><?php _e('Yes', self::$translation_domain); ?></option>
			<option value="no" <?php echo ("no" == get_option('tweet_button_related_follow_author', 'no'))?'checked="checked"':''; ?>><?php _e('No', self::$translation_domain); ?></option>
		</select></td>
	</tr>
</table>
<table>
	<tr valign="top">
		<th colspan="2" align="left"><?php _e('Count box position', self::$translation_domain) ?>:</th>
	</tr>
<?php foreach (self::$count_orientations as $orientation) { ?>
	<tr valign="top">
		<td><input type="radio"
			id="tweet_button_count<?php print $orientation; ?>"
			name="tweet_button_count" value="<?php echo $orientation; ?>"
			<?php echo ($orientation == get_option('tweet_button_count', 'horizontal'))?'checked="checked"':''; ?> />
		</td>
		<td><label for="tweet_button_count<?php echo $orientation; ?>"><?php _e($orientation, self::$translation_domain); ?></label></td>
		<td><a class="twitter-share-button" data-count="<?php echo $orientation; ?>" href="http://twitter.com/share">Tweet</a></td>
	</tr>
	<?php } ?>
</table>
<table>
	<tr valign="top">
		<th align="left"><label for="tweet_button_position"><?php _e('Button position', self::$translation_domain); ?>:</label></th>
		<td><select id="tweet_horizontalbutton_position" name="tweet_button_position">
			<option value="prepend" <?php echo ("prepend" == get_option('tweet_button_position', 'prepend'))?'checked="checked"':''; ?> ><?php _e('Before content', self::$translation_domain); ?></option>
			<option value="append" <?php echo ("append" == get_option('tweet_button_position', 'prepend'))?'checked="checked"':''; ?>><?php _e('After content', self::$translation_domain); ?></option>
		</select></td>
	</tr>
</table>

<h3><?php _e('Advanced options', self::$translation_domain) ?>:</h3>
<table>
	<tr valign="top">
		<th align="left"><label for="tweet_button_related"><?php _e('Suggestions to follow (related)', self::$translation_domain); ?>:</label></th>
		<td><input type="text" id="tweet_button_related" name="tweet_button_related" value="<?php echo get_option('tweet_button_related', 'mohanjith:S H Mohanjith'); ?>" /></td>
	</tr>
	<tr valign="top">
		<th align="left"><label for="tweet_button_lang"><?php _e('Language', self::$translation_domain); ?>:</label></th>
		<td><input size="3" type="text" id="tweet_button_lang" name="tweet_button_lang" value="<?php echo get_option('tweet_button_lang', 'en'); ?>" />
		</td>
	</tr>
	<tr valign="top">
		<th align="left"><label for="tweet_button_css"><?php _e('Inline styling', self::$translation_domain); ?>:</label></th>
		<td><textarea id="tweet_button_css" name="tweet_button_css" ><?php echo get_option('tweet_button_css', 'float: right;'); ?></textarea></td>
	</tr>
</table>

<input type="hidden" name="action" value="update" /> <input
	type="hidden" name="page_options"
	value="tweet_button_via,tweet_button_related_follow_author,tweet_button_count,tweet_button_position,tweet_button_related,tweet_button_lang,tweet_button_css" />

<p class="submit"><input type="submit" name="Submit"
	value="<?php _e('Save Changes', self::$translation_domain) ?>" /></p>
</form>
</div>
<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
	<?php
	}
}

// If we're not running in PHP 4, initialize
if (strpos(phpversion(), '4') !== 0) {
	// Initiate the plugin class
	$tweetbutton = new TweetButton();
}
