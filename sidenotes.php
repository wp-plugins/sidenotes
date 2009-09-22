<?php
/*
Plugin Name: Sidenotes
Plugin URI: http://www.uidesign.at
Description: This plugin provides the possibility to simply add short side notes to your wordpress blog (a linked title with some description). Simply activate it and add new side notes within the "Tools" admin panel. To show off your sidenotes just put <code>&lt;?php get_sidenotes(); ?&gt;</code> in your template. Enjoy!
Version: 0.9.2
Author: Stephan Lenhart
Author URI: http://www.uidesign.at
*/

if(isset($_REQUEST["sidenotes_uninstall"])) register_deactivation_hook(__FILE__, 'sidenotes_uninstall');
register_activation_hook(__FILE__,'sidenotes_install');
load_plugin_textdomain( 'sidenotes', FALSE, '/sidenotes/languages' );
add_action('admin_menu', 'sidenotes_menu');
add_filter('plugin_action_links', 'sidenotes_plugin_action', 10, 2);
add_filter('wp_meta', 'sidenotes_meta');
add_filter('wp_head', 'sidenotes_bloghead');
//add_action('template_redirect', 'sidenotes_required_files');
global $sidenotes_db_version, $sidenote_output_format;
$sidenotes_db_version = "0.9";
$sidenote_output_format = '<li><a href="%sidenote_url" title="%sidenote_title"><span class="date">%sidenote_date</span><br /><span class="text"><strong>%sidenote_title</strong> %sidenote_description</span></a></li>';

function sidenotes_install() {
	global $wpdb, $user_level, $sidenotes_db_version, $sidenote_output_format;
	
	add_option('sidenotes_max_number', '4', '', 'no');
	add_option('sidenotes_feed_number', '20', '', 'no');
	add_option('sidenotes_output_format', $sidenote_output_format, '', 'no');

	// Check current default wp date format
	$wp_date_format = get_option('date_format');
	add_option('sidenotes_date_format', ''.$wp_date_format.'', '', 'no');
	
	$table_name = $wpdb->prefix . "sidenotes";
	if($wpdb->get_var("show tables like `$table_name`") != $table_name) {
		
		mysql_query("SET CHARACTER SET utf8");
		mysql_query("SET NAMES utf8");
	
		$sql = "CREATE TABLE `" . $table_name . "` ( 
			`id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`title` VARCHAR(255) NOT NULL, 			
			`url` VARCHAR(255) NOT NULL, 
			`description` TEXT NOT NULL, 
			`time_updated` VARCHAR(15) NOT NULL,
			`time_published` VARCHAR(15) NOT NULL,			
			PRIMARY KEY ( `id` )
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		add_option("sidenotes_db_version", $sidenotes_db_version);
	}
}

function sidenotes_uninstall() {
	delete_option('sidenotes_db_version');
	delete_option('sidenotes_max_number');
	delete_option('sidenotes_date_format');
	delete_option('sidenotes_output_format');
	delete_option('sidenotes_feed_number');
}

function sidenotes_menu() {
	add_management_page('Sidenotes', 'Sidenotes', 8, 'sidenotes_posts', 'sidenotes_posts');
	add_options_page('Sidenotes', 'Sidenotes', 8, 'sidenotes_settings', 'sidenotes_settings');
}

function sidenotes_bloghead() {
	echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"RSS\" href=\"".get_bloginfo('siteurl')."/sidenotes-rss.php\" />";
}

function sidenotes_meta() {
	echo "<li><a href='".get_bloginfo('url')."/sidenotes-rss.php'>RSS SideNotes</a></li>";
}

/*
function sidenotes_required_files() {
	wp_register_style('sidenotes', WP_PLUGIN_URL . '/sidenotes/sidenotes.css', array(), '1.0', 'screen');	
	wp_enqueue_style('sidenotes');
}
*/

function sidenotes_plugin_action($links, $file) {
	static $this_plugin;
	
	if( empty($this_plugin) ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=sidenotes_settings' ) . '">' . __('Settings', 'sidenotes') . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}

function get_sidenotes() {
	global $wpdb;
	$sidenotes_table = $wpdb->prefix . "sidenotes";
	
	// Get Wordpress date format
	$sidenotes_date_format = get_option('sidenotes_date_format');
	
	if(get_option('sidenotes_max_number') != "") {
		$tmp_sql = " LIMIT 0, ".get_option('sidenotes_max_number');
	}
	
	// Get sidenotes
	$ResSidenotes = $wpdb->get_results( "SELECT id,title,url,description,time_published FROM $sidenotes_table ORDER BY time_published DESC".$tmp_sql );
	
	$format = get_option('sidenotes_output_format');
	
	$all_sidenotes = "";
	foreach($ResSidenotes as $result){
		$output = $format;
		
		$output = str_replace("%sidenote_url", $result->url, $output);
		$output = str_replace("%sidenote_title", $result->title, $output);
		$output = str_replace("%sidenote_description", $result->description, $output);
		$output = str_replace("%sidenote_date", date_i18n($sidenotes_date_format,$result->time_published), $output);
	
		$all_sidenotes .= $output;
	}
	$all_sidenotes .= "\n";
	echo $all_sidenotes;
}

function sidenotes_settings() {
	global $sidenote_output_format;
	?>
	<div id="sidenotes" class="wrap">
		<?php
		if(function_exists(screen_icon)) {
			screen_icon();
		};
		?>
		<h2>
			<?php _e('Sidenotes Settings', 'sidenotes'); ?>
		</h2>
		<script>
			function sidenotesTakeToCustom(obj) {
				top.document.getElementById('sidenotes_date_format').value = obj.value;
			}
			function sidenotesChangeDate(obj) {
				top.document.getElementById('sidenotes_date_custom_radio').checked = 'checked';
			}
			function sidenotesResetDefaultFormat() {
				var defaultFormat = '<?php echo $sidenote_output_format; ?>';
				top.document.getElementById('sidenotes_output_format').innerText = defaultFormat;
			}
		
		</script>
		<style>
			#sidenotes .legend {
				width: 170px; position: relative; float: left;
			}
		</style>
		
		<form method="post" action="options.php">
			<?php wp_nonce_field('update-options'); ?>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="sidenotes_max_number"><?php _e("Blog pages show at most", "sidenotes"); ?></label></th>
					<td>
						<input type="text" name="sidenotes_max_number" class="small-text" value="<?php echo get_option('sidenotes_max_number'); ?>" />
						<?php _e("sidenotes", "sidenotes"); ?><br />
						<span class="description"><?php _e("Leave blank to show all", "sidenotes"); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="sidenotes_feed_number"><?php _e("Syndication feeds show the most recent", "sidenotes"); ?></label></th>
					<td>
						<input type="text" name="sidenotes_feed_number" class="small-text" value="<?php echo get_option('sidenotes_feed_number'); ?>" />
						<?php _e("sidenotes", "sidenotes"); ?><br />
						<span class="description"><?php _e("Leave blank to show all", "sidenotes"); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e("Date format", "sidenotes"); ?></th>
					<td>
						<?php
						$sidenotes_date_format = get_option('sidenotes_date_format');
						$sidenotes_output_format = get_option('sidenotes_output_format');
						$wp_date_format = get_option('date_format');
						$arr_sidenotes_date_format = array();
						?>
						<fieldset>
							<legend class="screen-reader-text"><span>Date Format</span></legend>
							<label title='<?php echo $wp_date_format; ?>'><input type='radio' name='sidenotes_date' value='<?php echo $wp_date_format; ?>'<?php if($sidenotes_date_format == $wp_date_format) { echo " checked='checked'"; } ?> onClick='sidenotesTakeToCustom(this)' /> <?php echo date_i18n($wp_date_format,time()); ?></label><br />
							
							<label><input type="radio" name="sidenotes_date" id="sidenotes_date_custom_radio" value="<?php echo $sidenotes_date_format; ?>" <?php if($sidenotes_date_format != $wp_date_format) { echo " checked='checked'"; } ?> /> <?php _e("Custom", "sidenotes"); ?>: </label><input type="text" name="sidenotes_date_format" id="sidenotes_date_format" value="<?php echo $sidenotes_date_format; ?>" onFocus="sidenotesChangeDate(this)" class="middle-text" /> 
							<p><a href="http://codex.wordpress.org/Formatting_Date_and_Time"><?php _e("Documentation on date formatting", "sidenotes"); ?></a></p>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="sidenotes_output_format"><?php _e("Template for sidenote", "sidenotes"); ?></label></th>
					<td>
						<textarea name="sidenotes_output_format" id="sidenotes_output_format" class="large-text" rows="4"><?php echo $sidenotes_output_format; ?></textarea>
						<span class="description"><?php _e("Don't change it if you don't know! Only use double quotation marks!", "sidenotes"); ?></span>&nbsp;&nbsp;&nbsp;<a href="javascript:;" onClick="sidenotesResetDefaultFormat()"><?php _e("Reset to default", "sidenotes"); ?></a>
						<br /><br />
						<strong><?php _e("Legend", "sidenotes"); ?>:</strong>
						<br />
						<span class='nonessential legend'>%sidenote_title</span><?php _e("The title of your sidenote", "sidenotes"); ?><br />
						<span class='nonessential legend'>%sidenote_description</span><?php _e("The description of your sidenote", "sidenotes"); ?><br />
						<span class='nonessential legend'>%sidenote_url</span><?php _e("The URL in the &lt;a href...&gt; tag", "sidenotes"); ?><br />
						<span class='nonessential legend'>%sidenote_date</span><?php _e("The date you have published the sidenote", "sidenotes"); ?><br />
					</td>
				</tr>
			</table>

			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="sidenotes_max_number,sidenotes_feed_number,sidenotes_date_format,sidenotes_output_format" />
			
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'sidenotes') ?>" />
			</p>

		</form>
		
	</div>
	
	<?php
}
function sidenotes_posts() {
	global $wpdb;
	$reloadURI = get_option('siteurl') . '/wp-admin/tools.php?page=sidenotes_posts'; // Form Action URI
	
	mysql_query("SET CHARACTER SET utf8");
	mysql_query("SET NAMES utf8");
	
	$sidenotes_table = $wpdb->prefix . "sidenotes";
	$sidenotes_msg = "";
	
	// Show default http://
	if(!isset($_POST['sidenotes_url'])) {
		$sidenotes_url = "http://";
	}
	
	// Get Wordpress date format
	if(get_option('date_format') != "") {
		$sidenotes_dateformat = get_option('date_format');
	}
	
	// Get max number of sidenotes that are shown to user
	if(get_option('sidenotes_max_number') != "") {
		$sidenotes_max_number = get_option('sidenotes_max_number');
	}
	
	// Add new sidenote
	if(isset($_POST['sidenotes_add'])){
		$sidenotes_title = htmlentities($_POST['sidenotes_title'],ENT_COMPAT,"UTF-8");
		$sidenotes_url = clean_url($_POST['sidenotes_url'], $context = 'db');
		$sidenotes_description = htmlentities($_POST['sidenotes_description'],ENT_COMPAT,"UTF-8");
		
		if(empty($sidenotes_title) || empty($sidenotes_url) || empty($sidenotes_description)) {
			$sidenotes_msg .= __("Please insert all mandatory fields!", "sidenotes");
		} else {
			if(substr($sidenotes_url,0,7) != 'http://')
			$sidenotes_url = "http://".$sidenotes_url;
			
			$queryAddSN = "INSERT INTO $sidenotes_table (title, url, description, time_updated, time_published) VALUES ('$sidenotes_title', '$sidenotes_url', '$sidenotes_description','".time()."','".time()."')";
			$wpdb->query($queryAddSN);
			$sidenotes_msg .= __("Your sidenote has been successfully added!", "sidenotes");
			$sidenotes_title = "";
			$sidenotes_url = "";
			$sidenotes_description = "";			
		}
	}

	// Edit sidenote
	if(isset($_POST['sidenotes_edit']) && !isset($_POST['sidenotes_show_edit_id'])){
		$sidenotes_title =  htmlentities($_POST['sidenotes_title'],ENT_COMPAT,"UTF-8");
		$sidenotes_url = clean_url($_POST['sidenotes_url'], $context = 'db');
		$sidenotes_description =  htmlentities($_POST['sidenotes_description'],ENT_COMPAT,"UTF-8");
		$sidenotes_edit_id = $_POST['sidenotes_edit_id'];
		
		if(empty($sidenotes_title) || empty($sidenotes_url) || empty($sidenotes_description)) {
			$sidenotes_msg .= __("Please insert all mandatory fields!", "sidenotes");
		} else {
			$queryEditSN = "UPDATE $sidenotes_table SET url = '$sidenotes_url', title = '$sidenotes_title', description = '$sidenotes_description', time_updated = '".time()."' WHERE id = '$sidenotes_edit_id'";
			$wpdb->query($queryEditSN);
			
			$sidenotes_msg .= __("Your sidenote has been successfully updated!", "sidenotes");
			$sidenotes_title = "";
			$sidenotes_url = "";
			$sidenotes_description = "";
			$sidenotes_edit_id = "";
			unset($_POST['sidenotes_edit']);
		}		
	}
	
	// Delete sidenote
	if(isset($_POST['sidenotes_delete'])){
		$sidenotes_delete_id = $_POST['sidenotes_delete_id'];
		$queryDeleteSN = "DELETE FROM $sidenotes_table WHERE id = $sidenotes_delete_id";
		$wpdb->query($queryDeleteSN);
		
		$sidenotes_msg .= __("Your sidenote has been deleted!", "sidenotes");
	}
	
	// Complete messsage output
	if($sidenotes_msg != "") {
		$sidenotes_msg = '<div id="message" class="updated fade"><p>'.$sidenotes_msg.'</p></div>';
	}
	
	
	?>
	<div id="sidenotes" class="wrap">
		<?php
		if(function_exists(screen_icon)) {
			screen_icon();
		};
		?>
		<h2>
			<?php _e('Sidenotes posts', 'sidenotes'); ?>
		</h2>
		
		<?php echo $sidenotes_msg; ?>
		<?php wp_nonce_field('update-sidenotes-article'); ?>
			
		<h3>
		<?php 
		if(!isset($_POST['sidenotes_edit'])) {
			_e("Add a new sidenote to your blog", "sidenotes");
		} elseif(isset($_POST['sidenotes_show_edit_id'])) {
			$sidenotes_edit_id = $_POST['sidenotes_show_edit_id'];
			$ResEditSidenotes = $wpdb->get_row( "SELECT * FROM $sidenotes_table WHERE id = $sidenotes_edit_id" );
			
			$sidenotes_id = $ResEditSidenotes->id;
			$sidenotes_title = $ResEditSidenotes->title;
			$sidenotes_url = $ResEditSidenotes->url;
			$sidenotes_description = $ResEditSidenotes->description;
			_e("Edit sidenote", "sidenotes");
			echo ": &bdquo;".$sidenotes_title."&rdquo;";
		}
		?>
		</h3>
		
		<form method="post" action="<?php echo $reloadURI ?>" accept-charset="UTF-8" >
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e("Title", "sidenotes"); ?></th>
					<td>
						<input type="text" name="sidenotes_title" class="regular-text" value="<?php echo $sidenotes_title; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e("Url", "sidenotes"); ?></th>
					<td>
						<input type="text" name="sidenotes_url" class="regular-text" value="<?php echo $sidenotes_url; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e("Description", "sidenotes"); ?></th>
					<td>
						<textarea name="sidenotes_description" class="large-text" rows="5"><?php echo $sidenotes_description; ?></textarea>
					</td>
				</tr>	
			</table>
	
			<p class="submit">
			<?php
			
			if(isset($_POST['sidenotes_edit'])) {	
				echo "<input type='submit' class='button-primary' name='sidenotes_edit' value='". __('Edit Sidenote', 'sidenotes') ."'/>";
				echo "<input type='hidden' name='sidenotes_edit_id' value='$sidenotes_edit_id' />";	
			} else {
				echo "<input type='submit' class='button-primary' name='sidenotes_add' value='". __('Add Sidenote', 'sidenotes') ."'/>";
			}
			?>
			</p>
		</form>
	<?php
	
		// Display all sidenotes
		$sidenotes_max_number_table = 100;
		$ResSidenotes = $wpdb->get_results( "SELECT id,title,url,description,time_published FROM $sidenotes_table ORDER BY time_published DESC LIMIT 0,".$sidenotes_max_number_table );
		
		$ResSidenotesNumber = mysql_affected_rows();
		
		$tbl_output = "<h3>". __('All Sidenotes', 'sidenotes') ."</h3>";
		$tbl_output .= "
		<table class='widefat post fixed' cellpadding='0' cellspacing='0'>
			<thead>
				<tr>
					<th width='10'>&nbsp;</th>
					<th>". __('Title', 'sidenotes') ."</th>
					<th>". __('Description', 'sidenotes') ."</th>
					<th>". __('URL', 'sidenotes') ."</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
		";
		$countresults = 0;
		foreach($ResSidenotes as $result){
			$published = "";
			if($countresults < $sidenotes_max_number)
				$published = "<span class='nonessential'>[". __('Published', 'sidenotes')."]</span>";
			if($result->id == $sidenotes_edit_id)
				$tbl_output .= "<tr class='updated fade'>";
			else
				$tbl_output .= "<tr class=''>";
			$tbl_output .= "
				<td class='nonessential'>$ResSidenotesNumber</td>
				<td><strong>".$result->title." $published</strong><br /><span class='nonessential'>".date_i18n($sidenotes_dateformat,$result->time_published)."</span></td>
				<td>".$result->description."</td>
				<td>".$result->url."</td>
				<td>&nbsp;
					<div style='float: left'>
						<form action='$reloadURI' method='post'>
							<input type='submit' name='sidenotes_edit' class='button-secondary action' value='". __('Edit', 'sidenotes') ."'/>
							<input name='sidenotes_show_edit_id' type='hidden' value='".$result->id."'/>
						</form>
					</div>
					<div style='float: left; padding-left: 10px;'>
						<form id='sidenotesDeleteForm' action='$reloadURI' method='post'>
							<input type='submit' name='sidenotes_delete' class='button-secondary action' value='". __('Delete', 'sidenotes') ."'/>
							<input name='sidenotes_delete_id' type='hidden' value='".$result->id."'/>
						</form>
					</div>
				</td>
			</tr>
			";
			$countresults++;
			$ResSidenotesNumber--;
		}

		if(count($ResSidenotes) == 0) {
			$tbl_output .= "<tr><td colspan='5'>".__('You currently do not have any sidenotes', 'sidenotes')."</td></tr>";
		}		
		$tbl_output .= "
				</tbody>
			</table>
		";
		
		echo $tbl_output;
		
		if($countresults>$sidenotes_max_number_table) {
			echo "<span class='nonessential'>". __('Only the last 100 side notes will be displayed', 'sidenotes') . "</span>";
		}
	?>
	</div>
	<?php
}
?>