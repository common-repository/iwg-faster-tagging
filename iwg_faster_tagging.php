<?php
/*
Plugin Name: IWG Faster Tagging
Plugin URI: http://www.im-web-gefunden.de/wordpress-plugins/iwg-faster-tagging/
Description: "Faster Tagging" - A Plugin for faster tagging your posts
Author: Thomas Schneider
Author URI: http://www.im-web-gefunden.de/
Version: 1.2.0
License: GPL

	Copyright (C) 2008 Thomas Schneider
	http://www.im-web-gefunden.de/

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class IWG_FasterTagging {

	function IWG_FasterTagging(){	
	}

	function faster_tagging_init() {
		add_action('init', array('IWG_FasterTagging', 'faster_tagging_init_actions'));
		add_action('admin_head', array('IWG_FasterTagging', 'faster_tagging_head'));
		add_action('simple_edit_form', array('IWG_FasterTagging', 'faster_tagging_create_tag_buttons'));
		add_action('edit_form_advanced', array('IWG_FasterTagging', 'faster_tagging_create_tag_buttons'));
	}

	function faster_tagging_init_actions() {
		load_plugin_textdomain('iwg_faster_tagging',$path = dirname(str_replace(ABSPATH, '/', __FILE__)).'/iwg_faster_tagging_stuff/languages');
	}
	
	function faster_tagging_head() {
		$baseURL = dirname(str_replace(ABSPATH, '/', __FILE__)).'/iwg_faster_tagging_stuff';
		$baseURL = get_settings('siteurl') . $baseURL;
		echo '
			<link rel="stylesheet" href="'.$baseURL.'/style.css" type="text/css" />
			<script type="text/javascript" src="'.$baseURL.'/iwg_faster_tagging.js"></script>
			';
		if (function_exists('get_site_option') ) {
			$dis_auto_sug = get_site_option('iwg_fast_tagging_dis_auto_sug');
		} else {
			$dis_auto_sug = get_option('iwg_fast_tagging_dis_auto_sug');
		}
		if ( $dis_auto_sug ) {
		echo '
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery("#newtag").unbind("keypress");
				});
			</script>
			';
		}
	}
	
	function faster_tagging_create_tag_buttons() {
		// hide_empty = false dont't work in 2.5.1 must be 0! Why?
		$tagArray=get_terms('post_tag', 'hide_empty=0&fields=names&orderby=name');
		$taglist='';
		if($tagArray) {
			$tagList='<p>'.__('Your existing tags are: ','iwg_faster_tagging').'</p><div>';
			foreach($tagArray as $tag) {
				if($tag != '') {
					$tag=str_replace(array('"', "'"), array('&#34;', "\\'"), $tag);
					$tagList.='<input type="button" class="iwg_fast_tag_btn" onclick="javascript:iwg_addTag(this.value);" value="'.$tag.'" />';
				}
			}
			$tagList.='</div><p>'.__('Or type your tags here. You can also create new tags here (separate multiple tags with commas: Live, My World, At Home)', 'iwg_faster_tagging').'</p>';
			echo '
				<script type="text/javascript">

					addLoadEvent(iwg_showTags);
	
					function iwg_showTags() {
						jQuery(\'<div id="iwg_tagList">'.$tagList.'</div>\').insertBefore("#jaxtag");
						iwg_tag_update_quickclicks();
						//jQuery("#tags-input").show();
						tag_update_quickclicks=function() {
							//overwrite the original function and use this one
							iwg_tag_update_quickclicks();
						}
					};
				</script>
			';
		}
	}

	function add_options_page() {
		add_options_page(__('IWG Faster Tagging Options', 'iwg_faster_tagging'), 'IWG Faster Tagging', 'manage_options', 'iwg_faster_tagging_options', array('IWG_FasterTagging', 'options_page'));		
	}
	
	function options_page() {
		?>
		<div class="wrap" id="top">
			<h2><?php _e('IWG Faster Tagging Options', 'iwg_faster_tagging'); ?></h2>
			<form method="post" action="options.php">
			<?php wp_nonce_field('update-options'); ?>
			<h3><?php _e('Tag auto suggestion', 'iwg_faster_tagging'); ?></h3>
			<table summary="submit" class="form-table">
				<tr valign="top">
					<th scope="row" class="th-full">
						<label for="iwg_fast_tagging_dis_auto_sug">
							<input name="iwg_fast_tagging_dis_auto_sug" type="checkbox" id="iwg_fast_tagging_dis_auto_sug" value="1" <?php checked('1', get_option('iwg_fast_tagging_dis_auto_sug')); ?> />
							<?php _e('Disable tag auto suggestion', 'iwg_faster_tagging'); ?>
						</label>					
					</th>
				</tr>
			</table>
			<p class="submit">
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="iwg_fast_tagging_dis_auto_sug" />
				<input type="submit" name="Submit" value="<?php _e('Save Changes', 'iwg_faster_tagging') ?>" class="button" />
			</p>
			</form>
		</div>
		<?php
	}
	
	function wpmu_site_options_page() {
		?>
		<h3><?php _e('Tag Auto Suggestion <em>(Enable or disable for all blogs)</em>') ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e('Disable tag auto suggestion', 'iwg_faster_tagging'); ?></th>
					<th scope="row">
							<input name="iwg_fast_tagging_dis_auto_sug" type="checkbox" id="iwg_fast_tagging_dis_auto_sug" <?php checked('1', get_site_option('iwg_fast_tagging_dis_auto_sug')); ?> />
					</th>
				</tr>
			</table>
		<?php		
	}
	
	function wpmu_update_site_options() {
		if( is_site_admin() == false ) {
			wp_die( __('You do not have permission to access this page.') );
		}
		check_admin_referer('siteoptions');
		$dis_tag_sug = TRUE;
		if (empty($_POST['iwg_fast_tagging_dis_auto_sug']) || ($_POST['iwg_fast_tagging_dis_auto_sug'] != 'on')) {
			$dis_tag_sug = FALSE;
		}
		update_site_option('iwg_fast_tagging_dis_auto_sug', $dis_tag_sug);
	}
}

if ( (strstr($_SERVER['REQUEST_URI'], 'post-new.php') !== false) ||
	(strstr($_SERVER['REQUEST_URI'], 'post.php') !== false) ) {
	IWG_FasterTagging::faster_tagging_init();
} else {
	if (function_exists('get_site_option') ) {
		add_action('wpmu_options',array('IWG_FasterTagging', 'wpmu_site_options_page'));
		add_action('update_wpmu_options',array('IWG_FasterTagging', 'wpmu_update_site_options'));
	} else {
		add_action('admin_menu',array('IWG_FasterTagging', 'add_options_page'));
	}
}
?>
