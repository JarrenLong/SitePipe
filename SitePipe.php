<?php
/**
 * SitePipe, the simple website theme management API.
 *
 * @category   WebsiteBuilder
 * @package    SitePipe
 * @version    v0.0.x
 * @author     Jarren Long <jlong@booksnbytes.net>
 * @copyright  2018 Books N' Bytes, Inc.
 * @link       https://sitepipe.booksnbytes.net
 * @license    https://sitepipe.booksnbytes.net
 */


/**
 * Class for holding the master site configuration that dictates what 
 * SitePipe is capable of rendering. The SiteConfig works in tangent 
 * with the ThemeConfig class to power the SitePipe rendering engine.
 */
class SiteConfig {
	
	// {{{ Public fields
	
	/**
     * The title of the website
     */
	public $title = 'SitePipe';
	/**
     * The description of the website
     */
	public $description = 'Simple website theme management API';
	/**
     * The author of the website
     */
	public $author = 'Books N\' Bytes, Inc.';
	/**
     * URL to the author's website (not this website)
     */
	public $authorUrl = 'https://www.booksnbytes.net';
	/**
     * Logo to use for the site's favicons
     */
	public $logo = 'favicon.png';
	/**
     * Base directory for the site. All other paths will be relative to this.
     */
	public $baseDir = '.';
	/**
     * Directory containing website-specific resources
     */
	public $contentDir = 'content';
	/**
     * Directory containing theme packages
     */
	public $themesDir = 'themes';
	/**
	 * Directory where user posts are stored
	 */
	public $postsDir = 'posts';
	/**
     * Theme the site will use
     */
	public $themeId = 'default';
	/**
     * Array of NavBar objects
     */
	public $nav = array();
	/**
     * Array of Page objects
     */
	public $pages = array();
	
	// }}}
	// {{{ Public functions
	
	/**
	 * Creates a new SiteConfig instance by parsing the specified JSON data
	 *
	 * @param string $json  The JSON data to parse
	 */
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
		$this->postsDir = $this->baseDir . $json['postsDir'];
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
	
	// }}}
	
}

/**
 * Represents a navigation bar with links in it.
 */
class NavBar {
	
	// {{{ Public fields
	
	/**
	 * The name of this navbar
	 */
	public $name = 'default';
	/**
	 * Array of NavLink objects
	 */
	public $links = array();
	
	// }}}
	// {{{ Public functions
	
	/**
	 * Creates a new NavBar instance by parsing the specified JSON data
	 *
	 * @param string $json  The JSON data to parse
	 */
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
	
	// }}}
	
}

/**
 * Represent a single navigation link in a NavBar object.
 */
class NavLink {
	
	// {{{ Public fields
	
	/**
	 * The text to display for this navigation link
	 */
	public $title = '';
	/**
	 * The URL that this mav link will redirect to
	 */
	public $url = '';
	
	// }}}
	// {{{ Public functions
	
	/**
	 * Creates a new NavLink instance by parsing the specified JSON data
	 *
	 * @param string $json  The JSON data to parse
	 */
	public function __construct($json) {
		if($json == null)
			return;
		
		$this->title = $json['title'];
		$this->url = $json['url'];
	}
	
	// }}}
	
}

/**
 * Represents a Page that SitePipe is capable of rendering.
 */
class Page {
	
	// {{{ Public fields
	
	/**
	 * The name of this page
	 */
	public $name = '';
	/**
	 * The title text of this page
	 */
	public $title = '';
	/**
	 * Array of PageSection objects
	 */
	public $sections = array();
	
	// }}}
	// {{{ Public functions
	
	/**
	 * Creates a new Page instance by parsing the specified JSON data
	 *
	 * @param string $json  The JSON data to parse
	 */
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
	
	// }}}
	
}

/**
 * Represents a section of a Page that SitePipe can render.
 */
class PageSection {
	
	// {{{ Public fields
	
	/**
	 * The name of the template to use to render this section
	 */
	public $template = '';
	/**
	 * If specified, this is a markdown file to be rendered
	 */
	public $content = '';
	
	// }}}
	// {{{ Public functions
	
	/**
	 * Creates a new PageSection instance by parsing the specified JSON data
	 *
	 * @param string $json  The JSON data to parse
	 */
	public function __construct($json) {
		if($json == null)
			return;
		
		$this->template = $json['template'];
		$this->content = $json['content'];
	}
	
	// }}}
	
}

/**
 * Defines the theme that is being used by SitePipe for rendering Pages.
 */
class ThemeConfig {
	
	// {{{ Public fields
	
	/**
	 * Unique ID of the theme, MUST match the name of the directory it lives in
	 */
	public $id = 'default';
	/**
	 * The name of the theme
	 */
	public $name = 'Default Theme';
	/**
	 * The description of the theme
	 */
	public $description = 'Default theme included with SitePipe';
	/**
	 * The author of the website
	 */
	public $author = 'Books N\' Bytes, Inc.';
	/**
	 * URL to the author's website (not this website)
	 */
	public $authorUrl = 'https://www.booksnbytes.net';
	/**
	 * Array of ThemeTemplate objects
	 */
	public $templates = array();
	
	// }}}
	// {{{ Public functions
	
	/**
	 * Creates a new ThemeConfig instance by parsing the specified JSON data
	 *
	 * @param string $json  The JSON data to parse
	 */
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
	
	// }}}
	
}

/**
 * Defines a portion of the theme (a specific template) that can be used 
 * for rendering a Page.
 */
class ThemeTemplate {
	
	// {{{ Public fields
	
	/**
	 * The name of this theme template
	 */
	public $name = '';
	/**
	 * URL to the theme template file
	 */
	public $url = '';
	
	// }}}
	// {{{ Public functions
	
	/**
	 * Creates a new ThemeTemplate instance by parsing the specified JSON data
	 *
	 * @param string $json  The JSON data to parse
	 */
	public function __construct($json) {
		if($json == null)
			return;
		
		$this->name = $json['name'];
		$this->url = $json['url'];
	}
	
	// }}}
	
}


// Require that the MarkDoc engine be present
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'MarkDoc' . DIRECTORY_SEPARATOR . 'MarkDoc.php');


/**
 * The SitePipe class is the powerhouse of SitePipe. 
 * All of the magic happens here.
 */
class SitePipe {
	
	// {{{ Private fields
	
	/**
	 * Handle to the MarkDoc renderer
	 */
	private $md = null;
	/**
	 * SiteConfig object
	 */
	private $site = null;
	/**
	 * ThemeConfig object
	 */
	private $theme = null;
	/**
	 * Can be set to true if an AMP page is requested
	 */
	private $isAmp = false;
	
	// }}}
	// {{{ Public functions
	
	/**
	 * Creates a new SitePipe instance by parsing the specified JSON 
	 * config file.
	 *
	 * @param string $siteCfg  The SitePipe config file to parse
	 */
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
	
	/**
	 * Gets a handle to the site configuration.
	 *
	 * @returns a handle to the loaded SiteConfig object
	 */
	public function siteConfig() {
		return $this->site;
	}
	
	/**
	 * Gets a handle to the theme configuration.
	 *
	 * @returns a handle to the loaded ThemeConfig object
	 */
	public function themeConfig() {
		return $this->theme;
	}
	
	/**
	 * Indicates whether or not an AMP page was requested.
	 *
	 * @returns true if an AMP page was requested, else false.
	 */
	public function isAmpPage() {
		return $this->isAmp;
	}
	
	/**
	 * Handles rendering the site pipeline.
	 *
	 * @param $pageName  The name of the page to render, as defined in the 
	 *                   site configuration.
	 * @param $isAmp     Set to true if the AMP version of the page should 
	 *                   be rendered, else false for the standard version.
	 *
	 * @returns true on success, else false on error
	 */
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
			echo $this->md->processRequest($pageName);
		} else {
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
		}
		return true;
	}

	/**
	 * Wrapper for quickly rendering markdown docs
	 *
	 * @param $file  The markdown file to render. The theme resource directory 
	 *               will be checked for the file first. If not found, then 
	 *               the content directory will be checked. If the markdown
	 *               file is still not found, nothing will be rendered.
	 */
	public function renderMarkdown($file) {
		// If there is not theme function but a content file 
		// was defined, try to render it using MarkDoc.
		
		// Check for a theme resource file first
		$mdPath = $this->getThemeResource($file);
		if(!file_exists($mdPath)) {
			// If not found, check for a content file
			$mdPath = $this->getContentResource($file);
			// If not found, check for a posts file
			if(!file_exists($mdPath)) {
				$mdPath = $this->getPostResource($file);
			}
		}

		// If we found something, render it
		if(file_exists($mdPath)) {
			//$this->md->generateTOC('/articles', $this->site->postsDir . DIRECTORY_SEPARATOR, $this->site->postsDir . DIRECTORY_SEPARATOR);
			echo $this->md->renderPage($mdPath);
		}
	}
	
	/**
	 * HTML image tag helper with AMP support
	 *
	 * @param $url  URL to the image to dispaly
	 * @param $alt  The alternate text for the image if it cannot be rendered
	 * @param $id   The ID to use for the image tag
	 * @param $cls  The class(es) to use for the image tag
	 * @param $w    The width of the image
	 * @param $h    The height of the image
	 */
	public function HTML_Image($url, $alt, $id = '', $cls = '', $w = 0, $h = 0) {
		if($this->isAmp) {
			echo '<amp-img src="' . $url . '" alt="' . $alt . '" width="' . $w . '" height="' . $h . '" layout="responsive"><noscript><img src="' . $url . '" width="' . $w . '" height="' . $h . '" /></noscript></amp-img>';
		} else {
			echo '<img src="' . $url . '" alt="' . $alt . '" id="' . $id . '" class="' . $cls . '" />';
		}
	}
	
	/**
	 * Get info about the specified navigation bar.
	 *
	 * @param $name  The name of the navigation bar to get a handle to.
	 *
	 * @returns a handle to the nav bar if found, else null.
	 */
	public function getNavBar($name) {
		foreach($this->site->nav as $nb) {
			if($nb->name == $name) {
				return $nb;
			}
		}
		return null;
	}
	
	/**
	 * Builds a file path to the specified theme resource file.
	 *
	 * @param $url  The name of the file to get a path to
	 *
	 * @returns the full path to the theme resource file
	 */
	public function getThemeResource($url) {
		return $this->site->themesDir . DIRECTORY_SEPARATOR . $this->site->themeId . DIRECTORY_SEPARATOR . $url;
	}
	
	/**
	 * Builds a file path to the specified content resource file
	 *
	 * @param $url  The name of the file to get a path to
	 *
	 * @returns the full path to the content resource file
	 */
	public function getContentResource($url) {
		return $this->site->contentDir . DIRECTORY_SEPARATOR . $url;
	}
	
		/**
	 * Builds a file path to the specified content resource file
	 *
	 * @param $url  The name of the file to get a path to
	 *
	 * @returns the full path to the content resource file
	 */
	public function getPostResource($url) {
		return $this->site->postsDir . DIRECTORY_SEPARATOR . $url;
	}
	// }}}
	
}
?>
