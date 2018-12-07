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
		$this->contentDir = $json['contentDir'];
		$this->themesDir = $json['themesDir'];
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
	
	public function __construct() {
		// Load the MarkDoc parser
		$this->md = new MarkDoc();
		
		// Load the site config
		$string = file_get_contents("sitepipe-config.json");
		$json = json_decode($string, true);
		$this->site = new SiteConfig($json);
		
		// Load the theme config
		$themeConfig = $this->getThemeResource('theme-config.json');
		$string = file_get_contents($themeConfig);
		$json = json_decode($string, true);
		$this->theme = new ThemeConfig($json);
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
			}
		}
		
		if($pageTpl == null) {
			echo "Page Not Found<br/<br/>";
			
			echo "Site Config:\r\n";
			var_dump($this->site);
			echo "Theme Config:\r\n";
			var_dump($this->theme);
			return;
		}
		
		// Found the page to render, time to build it
		foreach($pageTpl->sections as $section) {
			foreach($this->theme->templates as $tpl) {
				if($section->template == $tpl->name) {
					echo $tpl->name .'<br/>';
					include_once($this->getThemeResource($tpl->url));
				}
			}
		}
		
		return;
		
		$this->curTemplate = $template;
		$themePath = 'themes/' . $this->active_theme_name . '/';
		
		// Load the theme to use. This registers all of our pages for this theme.
		include_once($themePath . 'index.php');
		setup_theme($this);
		
		// Include all of the registered theme files
		foreach($this->pages as $p) {
			include_once($themePath . $p->Url);
		}
		
		// Build the theme's navigation menu
		$this->nav_links = array();
		foreach($this->pages as $p) {
			if($p->ShowInNav) {
				if($this->curTemplate == 'home')
					array_push($this->nav_links, array($p->Id, $p->Name));
				else
					array_push($this->nav_links, array('index.php' . $p->Id, $p->Name));
			}
		}
		
		// Check if this was a request for an AMP page
		$this->isAmp = $_GET["amp"] != null;
		
		// If this request was for a specific page template, figure out which one
		$pageReq = '';
		$singlePage = false;
		$get_clean = htmlspecialchars($_GET["p"]);

		if($get_clean != null && strlen($get_clean) > 0) {
			// Render the specified page template
			$p = $this->get_page_by_name($get_clean);
			if($p == null)
				$p = $this->get_page_by_id('#' . $get_clean);
			
			if($p != null) {
				// Render the theme's primary header
				$this->render_page('#header');
				$this->render_page('#navbar', $nav);
				
				// Render the page itself
				$this->render_page($p->Id);
				
				// Render the theme's footer
				$this->render_page('#footer');
			} else {
				// TODO: Page not found ...
				//header('Location: .');
			}
		} else if($this->curTemplate != null && $this->curTemplate != '') {
			$json = $this->parseConfig();
			$arr = $json['pages'][$this->curTemplate];
			if($arr != null) {
				// Render the specified pages by ID
				foreach($arr as $p)
					$this->render_page($p);
			} else {
				// TODO: Page template not found ...
				//header('Location: .');
			}
		} else {
			// Render all pages
			foreach($this->pages as $p)
				$this->render_page($p->Id);
		}
		
		//http_response_code(200);
	}

	/* HTML tag helpers for AMP support */
	public function HTML_Image($url, $alt, $id = '', $cls = '', $w = 0, $h = 0) {
		if($this->isAmp) {
			echo '<amp-img src="' . $url . '" alt="' . $alt . '" width="' . $w . '" height="' . $h . '" layout="responsive"><noscript><img src="' . $url . '" width="' . $w . '" height="' . $h . '" /></noscript></amp-img>';
		} else {
			echo '<img src="' . $url . '" alt="' . $alt . '" id="' . $id . '" class="' . $cls . '" />';
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
