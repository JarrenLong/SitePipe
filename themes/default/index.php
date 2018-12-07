<?php
function setup_theme($pipe) {
  $pipe->register_page('Header', '#header', 'header.php', false, 'themes_default_header');
  $pipe->register_page('Navigation', '#navbar', 'navbar.php', false, 'themes_default_navbar');
  $pipe->register_page('SitePipe', '#sitepipe', 'sitepipe.php', true, 'themes_default_sitepipe');
  $pipe->register_page('Footer', '#footer', 'footer.php', false, 'themes_default_footer');
  
  $pipe->loadTheme();
  foreach($pipe->theme->templates as $tpl) {
	  include($tpl->url);
  }
}
?>
