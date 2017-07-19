<?php
/*
Plugin Name: Find and replacer
Plugin URI: http://www.wesg.ca/2008/08/wordpress-plugin-find-and-replacer/
Description: Easily find and replace text in your entire blog.
Version: 1.6
Author: Wes Goodhoofd
Author URI: http://www.wesg.ca/

This program is free software; you can redistribute it and/or
modify it under the terms of version 2 of the GNU General Public
License as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details, available at
http://www.gnu.org/copyleft/gpl.html
or by writing to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

//plugin function
function find_and_replacer() {
add_options_page('Find and Replacer 1.6', 'Find and Replacer', '8', __FILE__, 'find_and_replacer_admin');

}

//function to actually add to the database
function edit_query($first, $last, $find, $replace, $all, $content, $titles, $comments, $revisions) {
	global $wpdb;
$query_count = 0;

	//check if the form is empty
if (($all == '') && ($find == '') && ($replace == '')) {
	//build fault tolerance if the form is empty
	return __('There was no data submitted.', 'find_and_replacer');
	exit;
}

	//validate input page numbers
	if (($first != '') && ($last == ''))
		$last = $first;
	else if (($last != '') && ($first == ''))
		$first = $last;

	if ($find == '(*)')
		$replace_all = true;

	//main update query to build on
	$query = 'UPDATE ' . $wpdb->posts . ' SET ';

	//original select query to get requested pages
	if (isset($all)) {
	if (isset($content))
		$select = 'SELECT ID, post_content, post_title, post_status FROM ' . $wpdb->posts . ' ORDER BY ID DESC';
		//get comment text if required
		if (isset($comments))
			$select_comments = 'SELECT comment_ID, comment_post_ID, comment_content FROM ' . $wpdb->comments . ' ORDER BY comment_post_ID DESC'; }
	else {
	if (isset($content))
		$select = 'SELECT ID, post_content, post_title, post_status FROM  ' . $wpdb->posts . ' WHERE ID >= ' . $first . ' && ID <= ' . $last . ' ORDER BY ID DESC';
		//get comment text if required
		if (isset($comments))
			$select_comments = 'SELECT comment_ID, comment_post_ID, comment_content FROM  ' . $wpdb->comments . ' WHERE comment_post_ID >= ' . $first . ' && comment_post_ID <= ' . $last . ' ORDER BY comment_post_ID DESC'; }

	//get the records from the database
	$select_array = $wpdb->get_results($select); $query_count++;
	if (isset($comments)) {
		$comments_array = $wpdb->get_results($select_comments); $query_count++; }

	//characters not allowed in preg_replace
	$escape_chars = array('', '?', '^', '$', '.', '[', ']', '|', '(', ')', '*', '+', '{', '}', '>', '<', '/');
	$escape_replace = array('<\/');
	$x = 0;
	$search = array();
	$wildcard = 1;
		
	//decide which elements to search
	if ($titles == true)
		$search[] = 'post_title';
	if ($content == true)
		$search[] = 'post_content';
	if ($revisions == true)
		$revisions = 'inherit';
	else
		$revisions = 'false';
	if ($comments == true)
		$search[] = 'comments';

	$original_find = $find;

	preg_match('/\<([^<]+)\>/', $find, $matches3);

	$find = str_replace('(*)', '<wild>', $find);
	$replace = str_replace('(*)', '<wild>', $replace);
	
	//escape unallowed characters
	if (preg_match('/\<wild\>/', $find)) {

	foreach ($escape_chars as $chars) {
			$find = str_replace($chars, '\\' . $chars, $find);
			$replace = str_replace($chars, '\\' . $chars, $replace);
		$x++;
	}
	}

	//make the find and replace text compatible
	//don't let duplicates skip
	if (preg_match('/\(/', $find))
		$end = '\\)';
	else if (preg_match('/\[/', $find))
		$end = '\\]';
	else if (preg_match('/\{/', $find))
		$end = '\\}';
	else if (preg_match('/"/', $find))
		$end = '"';
	else
		$end = '<';

	$find = str_replace('\<wild\>', '([^' . $end .']+)', $find);
	
	for ($wildcard = 1; $wildcard <= substr_count($replace, '(*)')+1; $wildcard++) {
		$replace = str_replace('\<wild\>', '$' . $wildcard, $replace);
	}

	//initialize required variables
	$pages = $same = $blank_count = $confirm = 0;
	$result = $count = $updates = 0;
	$check = array();
 
	//edit all elements of the pages
	foreach($search as $elements) {

	//add comment list to execution array
	$return = $elements;
	if ($elements == 'comments') {
		$select_array = $comments_array;
		$elements = 'comment_content'; }

	//do the actual replacement
	//this is the heart of the plugin
	foreach ($select_array as $work) {
		$string_rep = $regex = 0;
		if ($elements == 'comments') {
			$status = $wpdb->get_var('SELECT post_status FROM ' . $wpdb->posts . ' WHERE ID = ' . $work->comment_post_ID); $query_count++;}

			if (($work->post_status != $revisions) && ($status != 'inherit')) {
			
	//change the entire text
	if ($replace_all == 'true') {
		if (preg_match('/\\$1/', $replace))
			$post = preg_replace('/\\$1/', $work->$elements, $replace);
		else
			$post = $replace;
		$string_rep = true;
		if ($elements == 'comment_content')
			$record = 'comment_post_ID';
		else
			$record = 'ID';
		//$check = array($work->$record); 
		$count = 1;
}
	else {
	//be able to compare the original post to the modified one
	$original_result = $result;
	if (!@preg_match('/\(\[\^/', $find)) {
		$post = str_replace($find, $replace, $work->$elements, $count);
		if ($count > 0)
			$string_rep = true;
	}


	//decide how to set up the wildcard
	$find = preg_replace('/\(\[\^.\]\+\)/', '(.*)', $find);

	if (!@preg_match('/\>(.*)\</', $matches1[1])) 
		$find = preg_replace('/\(\.\*\)/', '([^' . str_replace('\\', '', $end) . ']+)', $find);
		

	//check replacement text
	if (@preg_match('/' . $matches3[1] . '/', $matches1[1]))
			$find = @preg_replace('/\(\.\*\)/', '([^<]+)', $find);

	//it looks complicated, but it just checks for different conditions
		if (($string_rep != 1) && (@preg_match('#' . $find . '#', $work->$elements))) 		{
			$regex = true;

				//check if there are tags within tags
				if (preg_match('#<([^<]+)>(.*)<([^<]+)<(.*)<\/([^<]+)>#', $work->$elements) && preg_match('#<([^<]+)>([^<]+)<\/([^<]+)>#', $find, $matches)) {

			//if there are tags within tags, break them up into two queries
			preg_match('#<(.*)>(.*)<\/(.*)>#', $replace, $rmatches);
			$post = preg_replace('#<' . $matches[1] . '>#', '<' . $rmatches[1] . '>', $work->$elements, -1, $count);
			$temp = $count;
			$post = preg_replace('#<\/' . $matches[count($matches)-1] . '>#', '</' . $rmatches[count($rmatches)-1] . '>', $post, -1, $count);
			$count += $temp - 1;
			}
			else {

			//if there are no tags or anything complicated, do the normal switch
			if (!preg_match('/\>(.*)\</', $matches1[1])) 
				$find = str_replace('(.*)', '([^' . $end . ']+)', $find, $wild);
				$post = preg_replace('#' . $find . '#', $replace, $work->$elements, -1, $count);
			}
		}
	}

		if (($string_rep == true) || ($regex == true)) {
			//clean up post text
			$post = str_replace('\\', '', $post);
			$result += $count;
			$post = str_replace('<>', '', $post);
			$post = str_replace('</>', '', $post);
	
			//make sure to not double count pages
			foreach ($check as $compare) {
				if (($compare != $work->ID) && ($compare != $work->comment_post_ID))
					$in++;
				}
			
			if ($in == count($check)) {
				$pages++;
			if ($result > $original_result) {
				if ($elements == 'comment_content')
					$check[] = $work->comment_post_ID;
				else
					$check[] = $work->ID;
				}
			}
				$in = 0;

			//escape the quotation marks
			$post = str_replace('\'', "\\'", $post);
			$post = str_replace('"', '\\"', $post);
			//escape query-breaking code in post
			$post_chars = array('{', '}');

			if ($elements == 'comment_content') {
				foreach ($post_chars as $escape) {
				$post = str_replace($escape, '\\' . $escape, $post);
				}
			}



			//update the database
			if ($elements == 'comment_content') 
				$code = "UPDATE " . $wpdb->comments . " SET comment_content = '" . $post . "' WHERE comment_ID = " . $work->comment_ID;
			else
				$code = "UPDATE " . $wpdb->posts . " SET " . $elements . " = '" . $post . "', post_modified = '" . current_time(mysql) . "', post_modified_gmt = '" . gmdate('Y-m-d H:i:s') . "' WHERE ID = " . $work->ID;

			//choose the right element depending on selection
			if ($elements == 'comment_content')
					$record = 'comment_post_ID';
				else
					$record = 'ID';

		//check to see if there are blank posts and posts that haven't changed
		if (($post != '') && ($blank_count == 0) && ($post != $work->$elements)) {
			 if (end($check) == $work->$record) {
			$confirm++;
			if ($wpdb->query($code)) {
				$updates++; $query_count++;
				if ($elements == 'comment_content')
					$page_ID = $work->comment_post_ID;
				else
					$page_ID = $work->ID;
				}
			}
		}
		else if ($work->$elements != $post)
			$blank_count++;
			}
	}}
}
	
	//generate proper labels
	if ($result > 1)
		$label1 = 'changes';
	else
		$label1 = 'change';
	if ($pages > 1)
		$label2 = 'posts';
	else
		$label2 = 'post';

	//indicate whether changes were made successfully
	if ($blank_count > 0)
		$return = __('No changes were made because some posts were empty. <a href="javascript:history.go(-1)">Try again.</a>', 'find_and_replacer');
	else if (($confirm == $updates) && ($pages > 0) && ($updates > 0))
		$return = __('Successfully made ' . $result . ' ' . $label1 . ' on ' . $pages . ' ' . $label2 . ' with ' . $query_count . ' queries.', 'find_and_replacer');
	else if (($pages == 0) || ($confirm >= $updates))
		$return = __('There were no changes made. <a href="javascript:history.go(-1)">Try again.</a>', 'find_and_replacer');
	else
		$return = __('Error updating posts. <a href="javascript:history.go(-1)">Try again.</a>', 'find_and_replacer');

	return $return;
}

//function to display the admin panel
function find_and_replacer_admin() {
	global $wpdb;
	//load translations
load_plugin_textdomain('find_and_replacer', "wp-content/plugins/find-and-replacer/");

	//look for the hidden post data to know that the form was submitted
	if (isset($_POST['checker'])) {
		$return = edit_query($_POST['first_page'], $_POST['last_page'], $_POST['find_text'], $_POST['replace_text'], $_POST['all_pages'], $_POST['post_content'], $_POST['post_titles'], $_POST['post_comments'], $_POST['skip_revisions']);
		//print the result of the MySQL query in the pretty banner
		echo '<div id="message" class="updated fade"><p>' . $return . '</p></div>';
}
	//the rest is just form data for submission	
?>

<div class="wrap">
<div id="poststuff">
<h2><?php _e('Find and Replacer 1.6', 'find_and_replacer'); ?></h2>
<p><?php _e('This page offers a more powerful way to replace text <em>and tags</em> in your entire blog. Currently it can search through the post content and post title. Use regular text for normal replacements, but use <code>(*)</code> for wildcards. To replace tags, use <code>(*)</code> between the tags you wish to replace. Be sure to complete the proper options to make sure you do the right replacement. Whitespace must be exactly replicated.</p>
<p>For examples of proper syntax, visit <a href="http://www.wesg.ca/2008/08/wordpress-plugin-find-and-replacer/#examples">the plugin home page</a>.', 'find_and_replacer'); ?></p>

<div id="grabit" class="gbx-group">
<div class="postbox">
<h3><?php _e('Options', 'find_and_replacer'); ?></h3>
<div class="inside">
<table>
<form name="find_replacer" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<tr><td><?php _e('Starting ID to search', 'find_and_replacer'); ?></td><td><input type="text" name="first_page" size="4"></td></tr>
	<tr><td><?php _e('Last ID to search', 'find_and_replacer'); ?></td><td><input type="text" name="last_page" size="4"></td></tr>
	<tr><td><?php _e('Text to find', 'find_and_replacer'); ?></td><td><input type="text" name="find_text" size="42" ></td></tr>
<tr><td><?php _e('Text to replace', 'find_and_replacer'); ?></td><td><input type="text" name="replace_text" size="42"></td></tr>
	<tr><td><input type="checkbox" name="all_pages" value="all"> <?php _e('Search through all posts', 'find_and_replacer'); ?></td></tr>
<tr><td><input type="checkbox" name="skip_revisions" value="true" checked> <?php _e('Skip Revisions (only edit published posts and drafts)', 'find_and_replacer'); ?></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td><input type="checkbox" name="post_content" value="true" checked> <?php _e('Search post content', 'find_and_replacer'); ?></td></tr>
<tr><td><input type="checkbox" name="post_titles" value="true"> <?php _e('Search post titles', 'find_and_replacer'); ?></td></tr>
<tr><td><input type="checkbox" name="post_comments" value="true"> <?php _e('Search comment text', 'find_and_replacer'); ?></td></tr>

<input type="hidden" name="checker" value="OK">
<tr><td colspan="2"><p class="submit">
<input type="submit" name="Submit" value="<?php _e('Edit Pages') ?>" />
</p></td></tr>
</form>
</table>	
</div>
</div>
<?php
//this little doodad makes the lines alternate color
$inc = 0;
?>

<div class="postbox" id="listing">
<h3><?php _e('Page Listing', 'find_and_replacer'); ?></h3>
<div class="inside">
<table class="widefat">
<tr><td colspan="3"><?php _e('Sort by:', 'find_and_replacer'); ?> <a href="<?php echo edit_variable($_SERVER['REQUEST_URI'], '&sort', 'ID') . '#listing'; ?>"><?php _e('Post ID', 'find_and_replacer'); ?></a> / <a href="<?php echo edit_variable($_SERVER['REQUEST_URI'], '&sort', 'post_title') . '#listing'; ?>"><?php _e('Post Title', 'find_and_replacer'); ?></a> / <a href="<?php echo edit_variable($_SERVER['REQUEST_URI'], '&sort', 'post_date') . '#listing'; ?>"><?php _e('Post Date', 'find_and_replacer'); ?></a> / <a href="<?php echo edit_variable($_SERVER['REQUEST_URI'], '&sort', 'post_status') . '#listing'; ?>"><?php _e('Post status', 'find_and_replacer');?></a></td></tr>
<tr><td colspan="3"><?php _e('Number of posts to show:', 'find_and_replacer'); ?> <a href="<?php echo edit_variable($_SERVER['REQUEST_URI'], '&show', '10') . '#listing'; ?>">10</a> / <a href="<?php echo edit_variable($_SERVER['REQUEST_URI'], '&show', '50') . '#listing'; ?>">50</a> / <a href="<?php echo edit_variable($_SERVER['REQUEST_URI'], '&show', '100') . '#listing'; ?>">100</a></td></tr>
<tr><td colspan="3"><?php _e('Order of data:', 'find_and_replacer'); ?> <a href="<?php echo edit_variable($_SERVER['REQUEST_URI'], '&order', 'ASC') . '#listing'; ?>"><?php _e('Up', 'find_and_replacer'); ?></a> / <a href="<?php echo edit_variable($_SERVER['REQUEST_URI'], '&order', 'DESC') . '#listing'; ?>"><?php _e('Down', 'find_and_replacer'); ?></a></td></tr>

<tr><td width="10%"><strong><?php _e('Post ID', 'find_and_replacer'); ?></strong></td><td><strong><?php _e('Post Title', 'find_and_replacer'); ?></strong></td><td><strong><?php _e('Date posted', 'find_and_replacer'); ?></strong></td></tr>
<?php 
//determine how to display the page list
if (isset($_GET['sort']))
	$sort = $_GET['sort'];
else
	$sort = 'ID';

if (isset($_GET['show']))
	$show = $_GET['show'];
else
	$show = 10;

if (($sort == 'post_date') || ($sort == 'ID'))
	$order = 'DESC';
else
	$order = 'ASC';

if (isset($_GET['order']))
	$order = $_GET['order'];

//get the pages from the database
$page_ID = $wpdb->get_results("SELECT ID, post_title, post_date, post_status FROM $wpdb->posts ORDER BY $sort $order LIMIT $show");

foreach ($page_ID as $print) {
if ($inc % 2 != 0)
			$color = '<tr>';
		else
			$color = '<tr bgcolor="#ffffff">';

	$type = '';
if ($print->post_status == 'draft')
	$type = __(' (Draft)', 'find_and_replacer');
else if ($print->post_status == 'publish')
	$type = __(' (Published)', 'find_and_replacer');

echo $color . "<td>" . $print->ID . "</td><td><a href='" . get_permalink($print->ID) . "'>" . $print->post_title . "</a>" . $type . "</td>";
if ($print->post_date != '0000-00-00 00:00:00')
	echo "<td>" . date("F d, Y g:i A", strtotime($print->post_date)) . "</td>";
else
	echo "<td></td>";
echo "</tr>";

$inc++;
}
?>
</table>
</div>
</div>
</div>

<div id="grabit" class="gbx-group">
<div class="postbox">
<h3><?php _e('Plugin Information', 'mass_page_maker'); ?></h3>
<div class="inside">
<ul>
<li><a href="http://www.wesg.ca/">Wes G Homepage</a></li>
<li><a href="http://www.wesg.ca/2008/08/wordpress-plugin-find-and-replacer/" target="_blank">Plugin homepage</a></li>
<li><a href="http://www.wesg.ca/2008/08/wordpress-plugin-find-and-replacer/#changelog" target="_blank">Plugin changelog</a></li>
<li><a href="http://www.wesg.ca/2008/08/wordpress-plugin-find-and-replacer/#examples" target="_blank">Proper plugin usage</a></li>
<li><a href="http://wordpress.org/extend/plugins/find-and-replacer/" target="_blank">Plugin in Wordpress directory</a></li>
</ul>
</div>
</div>

</div>
</div>
</div>
<?php
}

function edit_variable($URL, $var, $value) {
//this function needs to remove the variable at the end
//it is far from elegant, but it gets the job done
if (substr_count($URL, $var) > 0) {
	$URL .= '#listing';
	$equal = substr_count($URL, '=');
	$amp = substr_count($URL, '&');

	if ($amp == ($equal - 1))
		$result = $var . '=' . $value . '&';
	else
		$result = $var . '=' . $value . '&';

	$URL = preg_replace('/' . $var . '=([^&]+)(&|#)/', $result . '$2', $URL);
	$URL = str_replace('&#', '#', $URL);
	$URL = str_replace('#listing', '', $URL);	}
else
	$URL .= $var . '=' . $value;$URL = substr($URL, strpos('%', $URL));
return $URL;
}

add_action('admin_menu', 'find_and_replacer');	//add the action so the blog knows it's there
?>