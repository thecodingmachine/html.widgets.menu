<?php
/*
 * Copyright (c) 2012-2013 David Negrier
 * 
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Html\Widgets\Menu;

use Mouf\Utils\Common\ConditionInterface\ConditionInterface;
use Mouf\Html\HtmlElement\HtmlElementInterface;
use Mouf\Html\Renderer\Renderable;

/**
 * This class represent a menu (full of menu items).
 * It is important to note that a menu item does not render directly in HTML (it has no toHtml method).
 * Instead, you must use another class (a Menu renderer class) to display the menu.
 * Usually, menu renderers are embedded into templates.
 * 
 */
class Menu implements MenuInterface, HtmlElementInterface {
	use Renderable;
	
	/**
	 * The children menu item of this menu (if any).
	 * 
	 * @var array<MenuItemInterface>
	 */
	private $children;
	
	/**
	 * This condition must be matched to display the menu.
	 * Otherwise, the menu is not displayed.
	 * The displayCondition is optional. If no condition is set, the menu will always be displayed. 
	 *
	 * @var ConditionInterface
	 */
	private $displayCondition;
	
	private $sorted = false;
	
	/**
	 * Constructor.
	 *
	 * @param array<MenuItemInterface> $children
	 */
	public function __construct(array $children = []) {
		$this->children = $children;
	}

	/**
	 * Returns a list of children elements for the menu (if there are some).
	 * @return array<MenuItemInterface>
	 */
	public function getChildren(): array {
		if ($this->sorted === false && $this->children !== []) {
			// First, let's make 2 arrays: the array of children with a priority, and the array without.
			$childrenWithPriorities = array();
			$childrenWithoutPriorities = array();
			foreach ($this->children as $child) {
				/* @var $child MenuItemInterface */
				$priority = $child->getPriority();
				if ($priority === null) {
					$childrenWithoutPriorities[] = $child;
				} else {
					$childrenWithPriorities[] = $child;
				}
			}
			
			usort($childrenWithPriorities, array($this, "compareMenuItems"));
			$this->children = array_merge($childrenWithPriorities, $childrenWithoutPriorities);
			$this->sorted = true;
		}
		return $this->children;
	}

	public function compareMenuItems(MenuItem $item1, MenuItem $item2): int {
		$priority1 = $item1->getPriority();
		$priority2 = $item2->getPriority();
		/*if ($priority1 === null && $priority2 === null) {
			// If no priority is set, let's keep the default ordering (which happens is usort by always returning positive numbers...) 
			return 1;	
		}*/
		return $priority1 <=> $priority2;
	}
	
	/**
	 * The children menu item of this menu (if any).
	 * 
	 * @Property
	 * @param array<MenuItemInterface> $children
	 */
	public function setChildren(array $children): void {
		$this->sorted = false;
		$this->children = $children;
	}
	
	/**
	 * Adds one child menu item to this menu item.
	 * 
	 * @param MenuItem $child
	 */
	public function addChild(MenuItem $child): void {
		$this->sorted = false;
		$this->children[] = $child;
	}
	
	/**
	 * If set, this display condition is tested. If it returns false, the menu will be hidden.
	 * 
	 * @Property
	 * @param ConditionInterface $displayCondition
	 */
	public function setDisplayCondition(ConditionInterface $displayCondition): void {
		$this->displayCondition = $displayCondition;
	}	
	

	/**
	 * If this function returns true, the menu item should not be displayed.
	 * 
	 * @return bool
	 */
	public function isHidden(): bool {
		if ($this->displayCondition == null) {
			return false;
		}
		return !$this->displayCondition->isOk();
	}
	
}
