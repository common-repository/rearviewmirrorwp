<?php
/*
Plugin Name: RearviewMirrorWP
Plugin URI: http://turmsegler.net/20070828/wp-plugin-rearview-mirror/
Description: Shows a link to the post closest to x months before the current date to remind users of older posts they might have missed before reading your weblog regularly. This link can optionally be appended to all your blog posts and RSS feed items.
Version: 1.0.1
Author: Benjamin Stein
Author URI: http://turmsegler.net/
*/

// $Id: RearviewMirrorWP.php 494518 2012-01-24 08:54:00Z turmsegler $
// $HeadURL: https://plugins.svn.wordpress.org/rearviewmirrorwp/trunk/RearviewMirrorWP.php $

// Lets add some default options if they don't exist
add_option('wprvm_days',         __(365, 'wprvm'));
add_option('wprvm_modify_feeds', true);
add_option('wprvm_modify_posts', true);
add_option('wprvm_start_day',    __(date('Y-m-d H:i:s'), 'wprvm'));
add_option('wprvm_title',        __('Last Years Post', 'wprvm'));
add_option('wprvm_cache_clean',  __(time(), 'wprvm'));

function getRearviewPostLink( $theBaseDate = '' )
{
    if ( '' == $theBaseDate )
        echo wp_rvm_get_post_link();
    else
        echo wp_rvm_get_post_link_for_date( $theBaseDate );
}

function wp_rvm_get_post_link ( )
{
    return wp_rvm_get_post_link_for_date( date('Y-m-d H') . ':00:00' );
}

function wp_rvm_get_post_link_for_date( $theBaseDate )
{
    $ltokens           = array(' ', ':');
    $rtokens           = array('_', '_');
    $cacheKey          = 'wprvm_' . md5( $theBaseDate );
    $wprvm_days        =  0;
    $thePostTitle      = '';
    $thePostUrl        = '';
    $thePostDate       = '';
    $result            = false;

    // try to fetch the output from WordPress transient cache
    $result = get_site_transient( $cacheKey );

    if ( false !== $result )
    {
        $result .= '<!-- wprvm cached -->';
    }

    // if it cannot be read from the cache
    // create the output dynamically

    if ( $result == false )
    {
        $wprvm_days   = get_option('wprvm_days');
        $resultsArray = array();

        $sql  = "  SELECT p.id, p.post_title, date_format( p.post_date, '%d. %m. %Y' ) as post_date_str ";
        $sql .= "    FROM wp_posts p  ";
        $sql .= "   WHERE p.post_date >= '".$theBaseDate."' - INTERVAL ".$wprvm_days." DAY ";
        $sql .= "     AND p.post_type = 'post' ";
        $sql .= "     AND p.post_status = 'publish' ";
        $sql .= "ORDER BY p.post_date ";
        $sql .= "   LIMIT 1 ";

        $res = mysql_query($sql) or die(mysql_error());
        $i = 0;

        if ($row = mysql_fetch_row($res))
        {
            $thePostUrl   = get_permalink($row[0]);
            $thePostTitle = $row[1];
            $thePostDate  = $row[2];

            $result = "<a title=\"".$thePostTitle." (".$thePostDate.")\" rel=\"bookmark\" href=\"".$thePostUrl."\">".$thePostTitle."</a> (".$thePostDate.")";

            set_site_transient( $cacheKey, $result, 86400 );
        }
    }

    return $result;
}

function wp_rvm_customize_feed( $content )
{
    global $post;

    $wprvm_modify_feeds = get_option('wprvm_modify_feeds');
    $wprvm_modify_posts = get_option('wprvm_modify_posts');

    if ( ( is_feed() && !$wprvm_modify_feeds ) || ( !is_feed() && !$wprvm_modify_posts ) )
        return $content;

    $wprvm_start_day = get_option('wprvm_start_day');

    if ( !empty($post->post_date) && strtotime($post->post_date) >= strtotime($wprvm_start_day) )
    {
        $wprvm_title = get_option('wprvm_title');
        $content    .= '<p class="postmetadata">'.$wprvm_title.':&nbsp;';
        $content    .= wp_rvm_get_post_link_for_date( $post->post_date );
        $content    .= '</p>';
    }

    return $content;
}

function wp_rvm_options_panel()
{
    // check form submission and update options
    if ('process' == $_POST['stage'])
    {
        update_option('wprvm_days',         $_POST['wprvm_days']);
        update_option('wprvm_modify_feeds', $_POST['wprvm_modify_feeds']);
        update_option('wprvm_modify_posts', $_POST['wprvm_modify_posts']);
        update_option('wprvm_start_day',    $_POST['wprvm_start_day']);
        update_option('wprvm_title',        $_POST['wprvm_title']);

        if(isset($_POST['wprvm_modify_feeds']))
           update_option('wprvm_modify_feeds', true);
        else
           update_option('wprvm_modify_feeds', false);

        if(isset($_POST['wprvm_modify_posts']))
           update_option('wprvm_modify_posts', true);
        else
           update_option('wprvm_modify_posts', false);
    }

    /* Get options */
    $wprvm_days         = get_option('wprvm_days');
    $wprvm_modify_feeds = get_option('wprvm_modify_feeds');
    $wprvm_modify_posts = get_option('wprvm_modify_posts');
    $wprvm_start_day    = get_option('wprvm_start_day');
    $wprvm_title        = get_option('wprvm_title');

    if ( $wprvm_modify_feeds )
        $wprvm_feed_flag = 'checked="checked"';
    else
        $wprvm_feed_flag = '';

    if ( $wprvm_modify_posts )
        $wprvm_post_flag = 'checked="checked"';
    else
        $wprvm_post_flag = '';

    echo
    '<div class="wrap">
      <h2>RearviewMirror Options</h2>
        <form name="form1" method="post" action="options-general.php?page=RearviewMirrorWP.php&updated=TRUE">
           <input type="hidden" name="stage" value="process" />
           <input type="hidden" name="wprvm_start_day" id="wprvm_start_day" value="'.$wprvm_start_day.'" />
               <table width="100%" cellspacing="2" cellpadding="5" class="editform">
               <tr valign="top">
                    <th scope="row">Horizon:</th>
                    <td><input name="wprvm_days" type="text" id="wprvm_days" value="'.$wprvm_days.'" size="50" />
                    <br />Rearview horizon in days</td>
               </tr>
               <tr valign="top">
                    <th scope="row">List Title:</th>
                    <td><input name="wprvm_title" type="text" id="wprvm_title" value="'.$wprvm_title.'" size="50" />
                    <br />Text shown in front of the real link</td>
               </tr>
               <tr valign="top">
                   <th scope="row">Modify Feeds:</th>
                   <td>
                   <input name="wprvm_modify_feeds" type="checkbox" id="wprvm_modify_feeds" value="wprvm_modify_feeds" '.$wprvm_feed_flag.' />
                   </td>
               </tr>
               <tr valign="top">
                   <th scope="row">Modify Posts:</th>
                   <td>
                   <input name="wprvm_modify_posts" type="checkbox" id="wprvm_modify_posts" value="wprvm_modify_posts" '.$wprvm_post_flag.' />
                   </td>
               </tr>
               <tr valign="top">
                    <th scope="row">Start Date:</th>
                    <td><input name="wprvm_start_day" type="text" id="wprvm_start_day" value="'.$wprvm_start_day.'" size="50" />
                    <br />Earliest publication time. Posts and RSS items published before <br />this timestamp will not be modified. (e. g. 2007-08-28 15:30:00)</td>
               </tr>
               </table>

        <p class="submit">
            <input type="submit" name="Submit" value="Update Options &raquo;" />
        </p>
        </form>

        <h2>Sample Output</h2>
        <p><strong>'.$wprvm_title.':</strong><ul>'.wp_rvm_get_post_link().'</ul></p>

    </div>';
}

function wp_rvm_add_options_page()
{
    if(function_exists("add_options_page"))
        add_options_page("RearviewMirror", "RearviewMirror", 5, basename(__FILE__), "wp_rvm_options_panel");
}


// Widget stuff
function widget_wp_rvm_init()
{
	if ( function_exists('register_sidebar_widget') ) :

    function widget_wp_rvm( $widget_args, $number = 1 )
    {
        extract($widget_args);
        $options     = get_option('widget_wp_rvm');
        $wprvm_title = get_option('wprvm_title');

        echo $before_widget;
        echo wp_rvm_get_post_link();
        echo $after_widget;
	}

	register_sidebar_widget('Rearview Mirror', 'widget_wp_rvm', null, 'RearviewMirrorWP');

    endif;
}


if(function_exists("add_action"))
    add_action("admin_menu"  , "wp_rvm_add_options_page");

if(function_exists("add_filter"))
    add_filter('the_content' , 'wp_rvm_customize_feed', 50);

if(function_exists("add_action"))
    add_action('init', 'widget_wp_rvm_init');

?>
