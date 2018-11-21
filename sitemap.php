<?php
/**
 * Generates a sitemap based on the base URL specified, and a list of all pages to be included.
 *
 * @param baseUrl  The base URL of the website
 * @param lastMod  The date that the website was last modified
 * @param urlList  An array of URLs to include in the sitemap. This should be a list of 
 *                 relative paths; the baseUrl will be prepended. This array should have 
 *                 the following structure:
 *                 {
 *                   'url': 'somepage.php',
 *                   'lastMod': '2018-11-21',
 *                 }, {
 *                   'url': 'subdir/anotherpage.php',
 *                   'lastMod': '2018-11-21',
 *                 }, ...
 * 
 * @return A string of the XML that represents the sitemap. This can be sent to the user 
 *         directly, or saved to a sitemap.xml file.
 */
function getSitemap($baseUrl, $lastMod, $urlList) {
  // header('Content-type: application/xml');
  $len = count($posts);

  $ret = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
  $ret .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
  $ret .= '<url>' . "\n";
  $ret .= '  <loc>' . $baseUrl . '</loc>' . "\n";
  $ret .= '  <lastmod>' . $lastMod . '</lastmod>' . "\n";
  $ret .= '  <changefreq>daily</changefreq>' . "\n";
  $ret .= '</url>' . "\n";

  for($i = 0; $i < $len; $i++) {
    $ret .= '<url>' . "\n";
    $ret .= '  <loc>' . $baseUrl . $urlList[$i]['url'] . '</loc>' . "\n";
    $ret .= '  <lastmod>' . $urlList[$i]['lastMod'] . '</lastmod>' . "\n";
    $ret .= '</url>' . "\n";
  }
  
  $ret .= '</urlset>';
  
  return $ret;
}
?>
