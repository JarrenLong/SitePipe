<?php
function theme_default_navbar($sp, $content) {
  if(!$sp->isAmpPage()) {
?>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
      <a class="navbar-brand" href="<?php echo $this->siteConfig()->authorUrl; ?>">
        <img src="images/sitepipe-512.png" alt="SitePipe" height="44px" />
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item active">
            <a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
          </li>
<?php
	$nav = $sp->getNavBar("main");
	foreach ($nav->links as $link) {
?>
          <li>
            <a class="nav-link" href="<?php echo $link->url ?>" title="<?php echo $link->title ?>"><?php echo $link->title ?></a></li>
          </li>
<?php
	}
?>
        </ul>
      </div>
    </nav>    
<?php
  }
}
?>
