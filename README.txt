$Id: README.txt 190689 2010-01-06 21:13:29Z turmsegler $
$HeadURL: https://plugins.svn.wordpress.org/rearviewmirrorwp/trunk/README.txt $

WordPress Plugin RearviewMirrorWP
Installation Notes

1) Upload RearviewMirrorWP.php to your WordPress plugin directory on the server (e. g. wp-content/plugins/)
2) Open the plugin administration panel and activate the plugin "RearviewMirrorWP"
3) You will find a new menu item "RearviewMirror" in the administration panels menu
4) Use this menu item to show the options panel
5) Modify the default settings as desired. Please make sure to use a correct format for the
   "Start Date".
6) If you activate the options "Modify Posts" and "Modify Feeds" the plugin will do just
   do the job for all posts and RSS items published AFTER the "Start Date".
7) If you want to show a link to your last years post in your sidebar, edit sidebar.php 
   and add a code snippet like this wherever you like:
   
   <h2>Last Years Post</h2>
   <ul>
      <li>
         <?php getRearviewPostLink(); ?>
      </li>
   </ul>
8) With current WordPress version including widget support you can just add the last years post by
   dropping the widget into your sidebar.
9) Updates might be available at http://turmsegler.net/20070828/wp-plugin-rearview-mirror/ and via
   the WordPress version update feature for automatic updates.

************************
Some words about caching
************************

WordPress > 2.0 is equipped with a powerful caching module that helps to significantly
reduce the load on your database. This plugin is caching the results of its queries in
case the following conditions are met

1) WordPress caching is enabled in your wp_config.php:

define ('ENABLE_CACHE', true);
define ('CACHE_EXPIRATION_TIME', 1800);

2) The folder your_wordpress_dir/wp_content/cache is writable by the Webserver

3) Your site runs with PHP Safe Mode Off


regards
Benjamin Stein
http://turmsegler.net
   