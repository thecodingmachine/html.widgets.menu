<?php
/*
 * Copyright (c) 2012 David Negrier
 * 
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Html\Widgets\Menu;

/**
 * This class represent a menu item target to add a target html attribute for the renderer.
 * It is important to note that a menu item does not render directly in HTML (it has no toHtml method).
 * Instead, you must use another class (a Menu renderer class) to display the menu.
 * 
 *
 * @Component
 */
class MenuItemTarget implements MenuItemStyleInterface {
	
	/**
	 * The target html attribute added to the menu item (default : "_blank")
	 *
	 * @var string
	 */
	public $target;

    public function __construct($target = "_blank") {
        $this->target = $target;
    }
	
	/**
	 * Returns the target html attribute or null if not set.
	 * @return string
	 */
	public function getTarget() {
        return $this->target;
	}
}
?>