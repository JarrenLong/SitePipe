<?php

class Page {
	public $Name = '';
	public $Id = '';
	public $Url = '';
	public $ShowInNav = false;
	public $Callback = '';
}

class SitePipe {
	private static $instance = null;
	
	public static function getInstance() {
		if(is_null(self::$instance))
			self::$instance = new self();
		return self::$instance;
	}
  
	private function __construct() {
		$this->pages = array();
		$this->active_theme_name = 'default';
		$this->nav_links = array();
	}
	
	private $pages = null;
	private $active_theme_name = '';
	private $nav_links = null;
	private $curTemplate = '';
	private $isAmp = false;
	
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
		return json_decode($string, true);
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
}
?>
