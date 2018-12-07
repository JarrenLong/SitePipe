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
	public $theme = 'default';
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
		$this->theme = $json['theme'];
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
				array_push($this->$pages, $p);
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
				array_push($this->$links, $l);
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
	public $title = '';
	public $sections = array();
	
	public function __construct($json) {
		if($json == null)
			return;
		
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
	// The name of the theme
	public $name = 'Default';
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
	public $id = '';
	public $url = '';
	
	public function __construct($json) {
		if($json == null)
			return;
		
		$this->name = $json['name'];
		$this->id = $json['id'];
		$this->url = $json['url'];
	}
}

require_once('./MarkDoc/MarkDoc.php');

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
		$this->md = new MarkDoc();
	}
	
	/* Start Page Management */
	public function register_page_class($page) {
		array_push($this->pages, $page);
	}
	public function register_page($n, $i, $u, $s, $cb = null) {
		$page = new Page();
		$page->Name = $n;
		$page->Id = $i;
		$page->Url = $u;
		$page->ShowInNav = $s;
		$page->Callback = $cb;
		array_push($this->pages, $page);
	}
	
	public function get_page_by_index($index) {
		return $this->pages[$index];
	}
	public function get_page_by_name($name) {
		foreach($this->pages as $p) {
			if(strtolower($p->Name) == strtolower($name))
				return $p;
		}
		return null;
	}
	public function get_page_by_id($id) {
		foreach($this->pages as $p) {
			if($p->Id == $id)
				return $p;
		}
		return null;
	}
	public function clear_pages() {
		unset($this->pages);
		$this->pages = array();
	}
	private function render_page($id, $args = null) {
		$p = $this->get_page_by_id($id);
		if($p != null && is_callable($p->Callback))
			call_user_func($p->Callback, $this, $args);
	}
	
	public function get_nav_links() {
		return $this->nav_links;
	}
	public function get_current_template() {
		return $this->curTemplate;
	}
	/* End Page Management */
	
	private function parseConfig() {
		$string = file_get_contents("sitepipe-config.json");
		$json = json_decode($string, true);
		$this->site = new SiteConfig($json);
	}
	
	public function isAmpPage() {
		return $this->isAmp;
	}
	
	public function render($template) {
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
	
	public function ThemePath($url) {
		
	}
}
?>
