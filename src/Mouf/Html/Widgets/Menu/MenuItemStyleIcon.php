<?php
/*
 * Copyright (c) 2012 David Negrier
 * 
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Html\Widgets\Menu;

/**
 * This class represent a menu item style icon to add an icon for the renderer.
 * It is important to note that a menu item does not render directly in HTML (it has no toHtml method).
 * Instead, you must use another class (a Menu renderer class) to display the menu.
 * 
 *
 * @Component
 */
class MenuItemStyleIcon implements MenuItemStyleInterface {
	
	/**
	 * The link for the icon (relative to the root url), unless it starts with / or http:// or https://.
	 *
	 * @var string
	 */
	private $url;
	
	/**
	 * Returns the URL for this menu (or null if this menu is not a link).
	 * @return string
	 */
	public function getUrl() {
		if (strpos($this->url, "/") === 0
			|| strpos($this->url, "javascript:") === 0
			|| strpos($this->url, "http://") === 0
			|| strpos($this->url, "https://") === 0) {
			return $this->url;	
		}
		return ROOT_URL.$this->url;
	}
	
	/**
	 * The link for the menu (relative to the root url), unless it starts with / or http:// or https://.
	 *
	 * @Property
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}	
}
?>