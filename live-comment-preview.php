<?php
/*
Plugin Name: Live Comment Preview
Plugin URI: http://wordpress.org/extend/plugins/live-comment-preview/
Description: Displays a preview of the user's comment as they type it.
Author: Brad Touesnard
Author URI: http://bradt.ca/
Version: 2.0.1

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/ 

function lcp_output_js() {
	global $user_ID, $user_identity;

	// Avatar settings
	$avatar_size = 32;
	$avatar_default = get_option('avatar_default');
	$avatar_rating = get_option('avatar_rating');

	$previewFormat = '';

	$file = TEMPLATEPATH . '/comment-preview.php';
	if (file_exists($file)) {
		ob_start();
		include($file);
		$previewFormat = ob_get_clean();

		// Get avatar size
		if (preg_match('@<img(.*?)class=.avatar(.*?)>@s', $previewFormat, $matches)) {
			$img_tag = $matches[0];
			
			if (preg_match('@width=.([0-9]+)@', $img_tag, $matches)) {
				$avatar_size = $matches[1];
			}
		}
	}

	$file = TEMPLATEPATH . '/comments.php';
	if (!$previewFormat && file_exists($file)) {
		global $wp_query, $comments, $comment, $post;
		
		$post->comment_status = 'open';
		
		$comment->comment_ID = 'lcp';
		$comment->comment_content = 'COMMENT_CONTENT';
		$comment->comment_author = 'COMMENT_AUTHOR';
		$comment->comment_parent = 0;
		$comment->comment_date = time();
		
		$wp_query->comment = $comment;
		$wp_query->comments = $comments = array($comment);
		$wp_query->current_comment = -1;
		$wp_query->comment_count = 1;
		
		ob_start();
		include($file);
		$html = ob_get_clean();

		if (preg_match('@<ol(.*?)class=.commentlist(.*)</ol>@s', $html, $matches)) {
			$previewFormat = $matches[0];
			
			$previewFormat = preg_replace('@http://COMMENT_AUTHOR_URL@', 'COMMENT_AUTHOR_URL', $previewFormat);
			
			if (preg_match('@<img(.*?)class=.avatar(.*?)>@s', $previewFormat, $matches)) {
				$img_tag = $matches[0];
				$new_img_tag = preg_replace('@src=("|\')(.*?)("|\')@', 'src=$1AVATAR_URL$3', $img_tag);
				$previewFormat = str_replace($img_tag, $new_img_tag, $previewFormat);
				
				if (preg_match('@width=.([0-9]+)@', $img_tag, $matches)) {
					$avatar_size = $matches[1];
				}
			}
		}
	}

	if ( !$avatar_default )
		$avatar_default = 'mystery';

	if ( is_ssl() ) {
		$host = 'https://secure.gravatar.com';
	} else {
		if ( !empty($email) )
			$host = sprintf( "http://%d.gravatar.com", ( hexdec( $email_hash{0} ) % 2 ) );
		else
			$host = 'http://0.gravatar.com';
	}
	
	if ( 'mystery' == $avatar_default )
		$avatar_default = "$host/avatar/ad516503a11cd5ca435acc9bb6523536"; // ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
	elseif ( 'blank' == $avatar_default )
		$avatar_default = includes_url('images/blank.gif');
	elseif ( 'gravatar_default' == $avatar_default )
		$avatar_default = "$host/avatar/";
	
	// Just in case the other two methods didn't work out
	if (!$previewFormat) {
		$previewFormat = '
			<ol class="commentlist">
				<li id="comment-preview">
					<img src="' . $avatar_default . '" alt="" class="avatar avatar-' . $avatar_size . '" width="' . $avatar_size . '" height="' . $avatar_size . '"/>
					<cite>COMMENT_AUTHOR</cite> Says:
					<br />
					COMMENT_CONTENT
				</li>
			</ol>';
	}
	
	// If you have changed the ID's on your form field elements
	// You should make them match here
	$commentFrom_commentID = 'comment';
	$commentFrom_authorID  = 'author';
	$commentFrom_urlID     = 'url';
	$commentFrom_emailID     = 'email';

	$user_gravatar = '';
	// Default name
	if ($user_ID) {
		$default_name = $user_identity;

		$user = get_userdata($user_ID);
		if ($user) {
			$user_gravatar = 'http://www.gravatar.com/avatar/' . md5(strtolower($user->user_email));
		}
	}
	else {
		$default_name = 'Anonymous';
	}

	// You shouldn't need to edit anything else.

	header('Content-type: text/javascript');
	global $allowedtags;
	?>

var allowedtags=['<?php echo implode("', '", array_keys($allowedtags)) ?>'];

function wptexturize(text) {
	text = ' '+text+' ';
	var next 	= true;
	var output 	= '';
	var prev 	= 0;
	var length 	= text.length;
	var tagsre = new RegExp('^/?(' + allowedtags.join('|') + ')\\b', 'i');
	while ( prev < length ) {
		var index = text.indexOf('<', prev);
		if ( index > -1 ) {
			if ( index == prev ) {
				index = text.indexOf('>', prev);
			}
			index++;
		} else {
			index = length;
		}
		var s = text.substring(prev, index);
		prev = index;
		if (output.match(/<$/) && !s.match(tagsre)) {
			// jwz: omit illegal tags
			output = output.replace(/<$/, ' ');
			s = s.replace(/^[^>]*(>|$)/, '');
		} else if ( s.substr(0,1) != '<' && next == true ) {
			s = s.replace(/---/g, '&#8212;');
			s = s.replace(/--/g, '&#8211;');
			s = s.replace(/\.{3}/g, '&#8230;');
			s = s.replace(/``/g, '&#8220;');
			s = s.replace(/'s/g, '&#8217;s');
			s = s.replace(/'(\d\d(?:&#8217;|')?s)/g, '&#8217;$1');
			s = s.replace(/([\s"])'/g, '$1&#8216;');
			s = s.replace(/([^\s])'([^'\s])/g, '$1&#8217;$2');
			s = s.replace(/(\s)"([^\s])/g, '$1&#8220;$2');
			s = s.replace(/"(\s)/g, '&#8221;$1');
			s = s.replace(/'(\s|.)/g, '&#8217;$1');
			s = s.replace(/\(tm\)/ig, '&#8482;');
			s = s.replace(/\(c\)/ig, '&#169;');
			s = s.replace(/\(r\)/ig, '&#174;');
			s = s.replace(/''/g, '&#8221;');
			s = s.replace(/(\d+)x(\d+)/g, '$1&#215;$2');
		} else if ( s.substr(0,5) == '<code' ) {
			next = false;
		} else {
			next = true;
		}
		output += s; 
	}
	return output.substr(1, output.length-2);	
}

function wpautop(p) {
	p = p + '\n\n';
	p = p.replace(/(<blockquote[^>]*>)/g, '\n$1');
	p = p.replace(/(<\/blockquote[^>]*>)/g, '$1\n');
	p = p.replace(/\r\n/g, '\n');
	p = p.replace(/\r/g, '\n');
	p = p.replace(/\n\n+/g, '\n\n');
	p = p.replace(/\n?(.+?)(?:\n\s*\n)/g, '<p>$1</p>');
	p = p.replace(/<p>\s*?<\/p>/g, '');
	p = p.replace(/<p>\s*(<\/?blockquote[^>]*>)\s*<\/p>/g, '$1');
	p = p.replace(/<p><blockquote([^>]*)>/ig, '<blockquote$1><p>');
	p = p.replace(/<\/blockquote><\/p>/ig, '<p></blockquote>');	
	p = p.replace(/<p>\s*<blockquote([^>]*)>/ig, '<blockquote$1>');
	p = p.replace(/<\/blockquote>\s*<\/p>/ig, '</blockquote>');	
	p = p.replace(/\s*\n\s*/g, '<br />');
	return p;
}

function updateLivePreview() {
	
	var cmntArea = document.getElementById('<?php echo $commentFrom_commentID ?>');
	var pnmeArea = document.getElementById('<?php echo $commentFrom_authorID ?>');
	var purlArea = document.getElementById('<?php echo $commentFrom_urlID ?>');
	var emlArea = document.getElementById('<?php echo $commentFrom_emailID ?>');
	
	if( cmntArea != null )
		var cmnt = wpautop(wptexturize(cmntArea.value));
	else
		var cmnt = '';

	if( pnmeArea != null )
		var pnme = pnmeArea.value;
	else
		var pnme = '';
	
	if( purlArea != null )
		var purl = purlArea.value;
	else
		var purl = '';
		
	if ( emlArea != null )
		var eml = emlArea.value;
	else
		var eml = '';
		
	if(purl && pnme) {
		var name = '<a href="' + purl + '">' + pnme + '</a>';
	} else if(!purl && pnme) {
		var name = pnme;
	} else if(purl && !pnme) {
		var name = '<a href="' + purl + '"><?php echo addslashes($default_name); ?></a>';
	} else {
		var name = "<?php echo addslashes($default_name); ?>";
	}	
	
	var user_gravatar = '<?php echo addslashes($user_gravatar); ?>';
	var gravatar = '<?php echo addslashes($avatar_default); ?>?';
	if (eml != '') {
		gravatar = 'http://www.gravatar.com/avatar/' + hex_md5(eml) + '?d=<?php echo urlencode($avatar_default); ?>&amp;';
	}
	else if (user_gravatar != '') {
		gravatar = user_gravatar + '?d=<?php echo urlencode($avatar_default); ?>&amp;';
	}
	
	gravatar += 's=<?php echo $avatar_size; ?>';
	
    <?php
	if (!empty($avatar_rating)) {
		?>
		gravatar += '&amp;r=<?php echo urlencode($avatar_rating) ?>';
		<?php
	}

    $previewFormat = str_replace("\r", "", $previewFormat);
    $previewFormat = str_replace("\n", "", $previewFormat);
    $previewFormat = str_replace("'", "\'", $previewFormat);
    $previewFormat = str_replace("COMMENT_AUTHOR", "' + name + '", $previewFormat);
    $previewFormat = str_replace("COMMENT_CONTENT", "' + cmnt + '", $previewFormat);
    $previewFormat = str_replace("AVATAR_URL", "' + gravatar + '", $previewFormat);
    $previewFormat = "'" . $previewFormat . "';\n";
    ?>
    document.getElementById('commentPreview').innerHTML = <?php echo $previewFormat; ?>
}

function initLivePreview() {
	if(!document.getElementById)
		return false;

	var cmntArea = document.getElementById('<?php echo $commentFrom_commentID ?>');
	var pnmeArea = document.getElementById('<?php echo $commentFrom_authorID ?>');
	var purlArea = document.getElementById('<?php echo $commentFrom_urlID ?>');
	
	if ( cmntArea )
		cmntArea.onkeyup = updateLivePreview;
	
	if ( pnmeArea )
		pnmeArea.onkeyup = updateLivePreview;
	
	if ( purlArea )
		purlArea.onkeyup = updateLivePreview;	
}

//========================================================
// Event Listener by Scott Andrew - http://scottandrew.com
// edited by Mark Wubben, <useCapture> is now set to false
//========================================================
function addEvent(obj, evType, fn){
	if(obj.addEventListener){
		obj.addEventListener(evType, fn, false); 
		return true;
	} else if (obj.attachEvent){
		var r = obj.attachEvent('on'+evType, fn);
		return r;
	} else {
		return false;
	}
}

addEvent(window, "load", initLivePreview);

	<?php
	// Add the MD5 functions using PHP so we only 
	// need to make 1 request to the web server for JS
	$plugin_path = dirname(__FILE__);
	$md5_file = $plugin_path . '/md5.js';
	@include($md5_file);
	
	// We're done outputting JS
	die();
}

function live_preview($before='', $after='') {
	global $livePreviewDivAdded;
	if($livePreviewDivAdded == false) {
		// We don't want this included in every page 
		// so we add it here instead of using the wphead filter
		echo '<script src="' . get_option('home') . '/?live-comment-preview.js" type="text/javascript"></script>';
		echo $before.'<div id="commentPreview"></div>'.$after;
		$livePreviewDivAdded = true;
	}
}

function lcp_add_preview_div($post_id) {
	live_preview();
	return $post_id;
}

$livePreviewDivAdded == false;

if( stristr($_SERVER['REQUEST_URI'], 'live-comment-preview.js') ) {
	add_action('template_redirect', 'lcp_output_js');
}

add_action('comment_form', 'lcp_add_preview_div');
