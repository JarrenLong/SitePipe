<?php

class SiteConfig {
	// The title of the website
	public $title = 'SitePipe';
	// The description of the website
	public $description = 'Simple website theme management API';
	// The author of the website
	public $author = 'Books N\' Bytes, Inc.';
	// URL to the author's website (not this website)
	public $authorUrl = 'https://www.booksnbytes.net';
	// Logo to use for the site's favicons
	public $logo = 'favicon.png';
	// Base directory for the site. All other paths will be relative to this.
	public $baseDir = '.';
	// Directory containing website-specific resources
	public $contentDir = 'content';
	// Directory containing theme packages
	public $themesDir = 'themes';
	// Theme the site will use
	public $themeId = 'default';
	// Array of NavBar objects
	public $nav = array();
	// Array of Page objects
	public $pages = array();
	
	public function __construct($json) {
		if($json == null)
			return;
		
		$this->title = $json['title'];
		$this->description = $json['description'];
		$this->author = $json['author'];
		$this->authorUrl = $json['authorUrl'];
		$this->logo = $json['logo'];
		$this->baseDir = $json['baseDir'];
		$this->contentDir = $this->baseDir . $json['contentDir'];
		$this->themesDir = $this->baseDir . $json['themesDir'];
		$this->themeId = $json['themeId'];
		$this->nav = array();
		$this->pages = array();
		
		// Parse the NavBar
		$navJson = $json['nav'];
		if($navJson != null) {
			foreach($navJson as $link) {
				$n = new NavBar($link);
				array_push($this->nav, $n);
			}
		}
		
		// Parse the Pages
		$pagesJson = $json['pages'];
		if($pagesJson != null) {
			foreach($pagesJson as $page) {
				$p = new Page($page);
				array_push($this->pages, $p);
			}
		}
	}
}
class NavBar {
	// The name of this navbar
	public $name = 'default';
	// Array of NavLink objects
	public $links = array();
	
	public function __construct($json) {
		if($json == null)
			return;
		
		$this->name = $json['name'];
		$this->links = array();
		
		// Parse the NavLinks
		$linksJson = $json['links'];
		if($linksJson != null) {
			foreach($linksJson as $link) {
				$l = new NavLink($link);
				array_push($this->links, $l);
			}
		}
	}
}
class NavLink {
	public $title = '';
	public $url = '';
	
	public function __construct($json) {
		if($json == null)
			return;
		
		$this->title = $json['title'];
		$this->url = $json['url'];
	}
}
class Page {
	public $name = '';
	public $title = '';
	public $sections = array();
	
	public function __construct($json) {
		if($json == null)
			return;
		
		$this->name = $json['name'];
		$this->title = $json['title'];
		$this->sections = array();
		
		$sectionsJson = $json['sections'];
		if($sectionsJson != null) {
			foreach($sectionsJson as $section) {
				$s = new PageSection($section);
				array_push($this->sections, $s);
			}
		}
	}
}
class PageSection {
	public $template = '';
	public $content = '';
	
	public function __construct($json) {
		if($json == null)
			return;
		
		$this->template = $json['template'];
		$this->content = $json['content'];
	}
}

class ThemeConfig {
	// Unique ID of the theme, MUST match the name of the directory it lives in
	public $id = 'default';
	// The name of the theme
	public $name = 'Default Theme';
	// The description of the theme
	public $description = 'Default theme included with SitePipe';
	// The author of the website
	public $author = 'Books N\' Bytes, Inc.';
	// URL to the author's website (not this website)
	public $authorUrl = 'https://www.booksnbytes.net';
	// Array of ThemeTemplate objects
	public $templates = array();
	
	public function __construct($json) {
		if($json == null)
			return;
		
		$this->id = $json['id'];
		$this->name = $json['name'];
		$this->description = $json['description'];
		$this->author = $json['author'];
		$this->authorUrl = $json['authorUrl'];
		$this->templates = array();
		
		$templateJson = $json['templates'];
		if($templateJson != null) {
			foreach($templateJson as $tpl) {
				$t = new ThemeTemplate($tpl);
				array_push($this->templates, $t);
			}
		}
	}
}
class ThemeTemplate {
	public $name = '';
	public $url = '';
	
	public function __construct($json) {
		if($json == null)
			return;
		
		$this->name = $json['name'];
		$this->url = $json['url'];
	}
}

require_once(__DIR__ . '/MarkDoc/MarkDoc.php');

class SitePipe {
	// Handle to the MarkDoc renderer
	private $md = null;
	// SiteConfig object
	private $site = null;
	// ThemeConfig object
	private $theme = null;
	// Can be set to true if an AMP page is requested
	private $isAmp = false;
	
	public function __construct($siteCfg = "sitepipe-config.json") {
		// Load the MarkDoc parser
		$this->md = new MarkDoc();
		
		// Load the site config
		$string = file_get_contents($siteCfg);
		$json = json_decode($string, true);
		$this->site = new SiteConfig($json);
		
		// Load the theme config
		$themeConfig = $this->getThemeResource('theme-config.json');
		$string = file_get_contents($themeConfig);
		$json = json_decode($string, true);
		$this->theme = new ThemeConfig($json);
	}
	
	public function siteConfig() {
		return $this->site;
	}
	
	public function themeConfig() {
		return $this->theme;
	}
	
	public function isAmpPage() {
		return $this->isAmp;
	}
	
	public function render($pageName, $isAmp = false) {
		$pageTpl = null;
				
		// Find the page that we will be building
		foreach($this->site->pages as $page) {
			if($page->name == $pageName) {
				$pageTpl = $page;
				break;
			}
		}
		
		// Bail out if we don't have a page to render
		if($pageTpl == null) {
			return false;
		}
		
		// Found the page to render, time to build it
		foreach($pageTpl->sections as $section) {
			foreach($this->theme->templates as $tpl) {
				if($section->template == $tpl->name) {
					include_once($this->getThemeResource($tpl->url));
					$themeFunc = 'theme_' . $this->theme->id . '_' . $tpl->name;
					if(is_callable($themeFunc)) {
						// Call the theme page function to render the section
						call_user_func($themeFunc, $this, $section->content);
					} else if($section->content != null && $section->content != '') {
						// If there is not theme function but a content file 
						// was defined, try to render it using MarkDoc.
						$this->renderMarkdown($section->content);
					}
				}
			}
		}
		
		return true;
	}

	// Wrapper for quickly rendering markdown docs
	public function renderMarkdown($file) {
		// If there is not theme function but a content file 
		// was defined, try to render it using MarkDoc.
		
		// Check for a theme resource file first
		$mdPath = $this->getThemeResource($file);
		if(!file_exists($mdPath)) {
			// If not found, check for a content file
			$mdPath = $this->getContentResource($file);
		}
		
		// If we found something, render it
		if(file_exists($mdPath)) {
			echo $this->md->renderPage($mdPath);
		}
	}
	
	/* HTML tag helpers for AMP support */
	public function HTML_Image($url, $alt, $id = '', $cls = '', $w = 0, $h = 0) {
		if($this->isAmp) {
			echo '<amp-img src="' . $url . '" alt="' . $alt . '" width="' . $w . '" height="' . $h . '" layout="responsive"><noscript><img src="' . $url . '" width="' . $w . '" height="' . $h . '" /></noscript></amp-img>';
		} else {
			echo '<img src="' . $url . '" alt="' . $alt . '" id="' . $id . '" class="' . $cls . '" />';
		}
	}
	
	public function getNavBar($name) {
		foreach($this->site->nav as $nb) {
			if($nb->name == $name) {
				return $nb;
			}
		}
	}
	
	public function getThemeResource($url) {
		return $this->site->themesDir . '/' . $this->site->themeId . '/' . $url;
	}
	public function getContentResource($url) {
		return $this->site->contentDir . '/' . $url;
	}
}
?>
