<?php
/*
 * Copyright (c) 2012 David Negrier
 * 
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Html\Widgets\Menu;

use Mouf\Utils\Common\ConditionInterface\ConditionInterface;
use Mouf\Utils\I18n\Fine\Translate\LanguageTranslationInterface;

/**
 * This class represent a menu item.
 * It is important to note that a menu item does not render directly in HTML (it has no toHtml method).
 * Instead, you must use another class (a Menu renderer class) to display the menu.
 * Usually, menu renderers are embedded into templates.
 * 
 *
 * @Component
 */
class MenuItem implements MenuItemInterface {
	
	/**
	 * The text for the menu item
	 *
	 * @var string
	 */
	private $label;
	
	/**
	 * The link for the menu (relative to the root url), unless it starts with / or http:// or https://.
	 *
	 * @var string
	 */
	private $url;
	
	/**
	 * The children menu item of this menu (if any).
	 * 
	 * @var array<MenuItemInterface>
	 */
	private $children;
	
	/**
	 * The CSS class for the menu, if any.
	 * Use of this property depends on the menu implementation.
	 *
	 * @var string
	 */
	private $cssClass;
	
	/**
	 * A list of parameters that are propagated by the link.
	 * For instance, if the parameter "mode" is set to 42 on the page (because the URL is http://mywebsite/myurl?mode=42),
	 * then if you choose to propagate the "mode" parameter, the menu link will have "mode=42" as a parameter.
	 *
 	 * @var array<string>
	 */
	private $propagatedUrlParameters;
	
	/**
	 * This condition must be matched to display the menu.
	 * Otherwise, the menu is not displayed.
	 * The displayCondition is optional. If no condition is set, the menu will always be displayed. 
	 *
	 * @var ConditionInterface
	 */
	private $displayCondition;
	
	/**
	 * The translation service to use (if any) to translate the label text.
	 * 
	 * @var LanguageTranslationInterface
	 */
	private $translationService;
	
	/**
	 * Whether the menu is in an active state or not.
	 * 
	 * @var bool
	 */
	private $isActive;
	
	/**
	 * Whether the menu is extended or not.
	 * This should not have an effect if the menu has no child.
	 * 
	 * @var bool
	 */
	private $isExtended;

	/**
	 * Level of priority used to order the menu items.
	 * 
	 * @var float
	 */
	private $priority;
	
	/**
	 * If the URL of the current page matches the URL of the link, the link will be considered as "active".
	 * 
	 * @var bool
	 */
	private $activateBasedOnUrl = true;
	
	/**
	 * @var array<MenuItemStyleInterface>
	 */
	private $additionalStyles = array();
	
	/**
	 * Constructor.
	 *
	 * @param string $label
	 * @param string $url
	 * @param array<MenuItemInterface> $children
	 */
	public function __construct($label=null, $url=null, $children=array()) {
		$this->label = $label;
		$this->url = $url;
		$this->children = $children;
	}

	/**
	 * Returns the label for the menu item.
	 * @return string
	 */
	public function getLabel() {
		if ($this->translationService) {
			return $this->translationService->getTranslation($this->label);
		} else {
			return $this->label;
		}
	}
	
	/**
	 * The label for this menu item.
	 * 
	 * @Property
	 * @Compulsory
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}
	
	/**
	 * Returns the URL for this menu (or null if this menu is not a link).
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}
	
	/**
	 * The link for the menu (relative to the root url), unless it starts with / or http:// or https://.
	 *
	 * @Property
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}	
	
	/**
	 * Returns a list of children elements for the menu (if there are some).
	 * 
	 * @see MenuItemInterface::getChildren()
	 * @return array<MenuItemInterface>
	 */
	public function getChildren() {
		if ($this->sorted == false && $this->children) {
			// First, let's make 2 arrays: the array of children with a priority, and the array without.
			$childrenWithPriorities = array();
			$childrenWithoutPriorities = array();
			foreach ($this->children as $child) {
				/* @var $child MenuItemInterface */
				$priority = $child->getPriority();
				if ($priority === null || $priority === "") {
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
	
	private $sorted = false;
	
	public function compareMenuItems(MenuItem $item1, MenuItem $item2) {
		$priority1 = $item1->getPriority();
		$priority2 = $item2->getPriority();
		/*if ($priority1 === null && $priority2 === null) {
			// If no priority is set, let's keep the default ordering (which happens is usort by always returning positive numbers...) 
			return 1;	
		}*/
		return $priority1 - $priority2;
	}
	
	/**
	 * The children menu item of this menu (if any).
	 * 
	 * @Property
	 * @param array<MenuItemInterface> $children
	 */
	public function setChildren(array $children) {
		$this->sorted = false;
		$this->children = $children;
		return $this;
	}

	/**
	 * Adds a menu item as a child of this menu item.
	 * 
	 * @param MenuItemInterface $menuItem
	 */
	public function addMenuItem(MenuItemInterface $menuItem) {
		$this->sorted = false;
		$this->children[] = $menuItem;
	}
	
	/**
	 * Returns true if the menu is in active state (if we are on the page for this menu).
	 * @return bool
	 */
	public function isActive() {
		if ($this->isActive) {
			return true;
		}
		// TODO: really compare URLs instead of performin a strpos.
		// We can do this using the parse_url function
		//var_dump(parse_url(ROOT_URL.$this->url));
		
		$requestUrl = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
		//error_log($_SERVER['REQUEST_URI'].' - '.$requestUrl.' - '.ROOT_URL.$this->url);
		if($this->activateBasedOnUrl) {
			if($_SERVER['REQUEST_URI'] == ROOT_URL.$this->url)
				return true;
			elseif($requestUrl == ROOT_URL.$this->url)
				return true;
		}
		/*
		if ($this->activateBasedOnUrl && $this->url && strpos($_SERVER['REQUEST_URI'], ROOT_URL.$this->url) !== false) {
			return true;
		}
		*/
		return false;
	}
	
	/**
	 * Set the active state of the menu.
	 * 
	 * @Property
	 * @param bool $isActive
	 */
	public function setIsActive($isActive) {
		$this->isActive = $isActive;
		return $this;
	}
	
	/**
	 * Enables the menu item (activates it).
	 * 
	 */
	public function enable() {
		$this->isActive = true;
		return $this;
	}
	
	/**
	 * Returns true if the menu should be in extended state (if we can see the children directly).
	 * @return bool
	 */
	public function isExtended() {
		return $this->isExtended;
	}

	/**
	 * Whether the menu is extended or not.
	 * This should not have an effect if the menu has no child.
	 * 
	 * @Property
	 * @param bool $isExtended
	 */
	public function setIsExtended($isExtended = true) {
		$this->isExtended = $isExtended;
		return $this;
	}
	
	/**
	 * Returns an optional CSS class to apply to the menu item.
	 * @return string
	 */
	public function getCssClass() {
		return $this->cssClass;
	}

	/**
	 * An optional CSS class to apply to the menu item.
	 * Use of this property depends on the menu implementation.
	 * 
	 * @Property
	 * @param string $cssClass
	 */
	public function setCssClass($cssClass) {
		$this->cssClass = $cssClass;
		return $this;
	}

	/**
	 * Level of priority used to order the menu items.
	 * 
	 * @Property
	 * @param float $priority
	 */
	public function setPriority($priority) {
		$this->priority = $priority;
		return $this;
	}

	/**
	 * Returns the level of priority. It is used to order the menu items.
	 * @return float
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * Returns the list of additionnal style
	 * @return array<MenuItemStyleInterface>
	 */
	public function getAdditionalStyles() {
		return $this->additionalStyles;
	}

	/**
	 * Returns the one of additionnal style
	 * @return array<MenuItemStyleInterface>
	 */
	public function getAdditionalStyleByType($type) {
		$return = null;
		if($this->additionalStyles) {
			foreach ($this->additionalStyles as $additionalStyle) {
				if($additionalStyle instanceof $type) {
					if($return === null)
						$return = $additionalStyle;
					else
						throw new \Exception("MenuItem: There are many instance of $type, please use getAdditionalStylesByType function to get all instance");
				}
			}
		}
		return $return;
	}

	/**
	 * Returns the list of additionnal style
	 * @return array<MenuItemStyleInterface>
	 */
	public function getAdditionalStylesByType($type) {
		$return = array();
		if($this->additionalStyles) {
			foreach ($this->additionalStyles as $additionalStyle) {
				if($additionalStyle instanceof $type) {
					$return[] = $additionalStyle;
				}
			}
		}
		return $return;
	}
	
	/**
	 * Add menu item style.
	 * 
	 * @Property
	 * @param array<MenuItemStyleInterface> $menuItemStyleInterface
	 */
	public function setAdditionalStyles($menuItemStyleInterface) {
		$this->additionalStyles = $menuItemStyleInterface;
		return $this;
	}
	
	/**
	 * Returns true if this menu item is a separator.
	 * A separator is a special case of menu item that is juste here to separate menu items with a bar.
	 * It has no label, no URL, etc...
	 * 
	 * @return bool
	 */
	public function isSeparator() {
		return false;
	}
	
	/**
	 * If this function returns true, the menu item should not be displayed.
	 * 
	 * @return bool
	 */
	public function isHidden() {
		if ($this->displayCondition == null) {
			return false;
		}
		return !$this->displayCondition->isOk();
	}

	/**
	 * If any translation service is set, it will be used to translate the label.
	 * Otherwise, the label is displayed "as-is".
	 * 
	 * @Property
	 * @param LanguageTranslationInterface $translationInterface
	 */
	public function setTranslationService(LanguageTranslationInterface $translationService) {
		$this->translationService = $translationService;
		return $this;
	}


	/**
	 * If set, this display condition is tested. If it returns false, the menu will be hidden.
	 * 
	 * @Property
	 * @param ConditionInterface $displayCondition
	 */
	public function setDisplayCondition(ConditionInterface $displayCondition) {
		$this->displayCondition = $displayCondition;
		return $this;
	}	
	
	/**
	 * A list of parameters that are propagated by the link.
	 * For instance, if the parameter "mode" is set to 42 on the page (because the URL is http://mywebsite/myurl?mode=42),
	 * then if you choose to propagate the "mode" parameter, the menu link will have "mode=42" as a parameter.
	 *
	 * @Property
	 * @param array<string> $propagatedUrlParameters
	 */
	public function setPropagatedUrlParameters($propagatedUrlParameters) {
		$this->propagatedUrlParameters = $propagatedUrlParameters;
		return $this;
	}
	
	
	/**
	 * Returns the absolute URL, with parameters if required.
	 * @return string
	 */
	public function getLink() {
		if (!$this->url) {
			return null;
		}
		$link = $this->getLinkWithoutParams();
		
		$params = array();
		// First, get the list of all parameters to be propagated
		if (is_array($this->propagatedUrlParameters)) {
			foreach ($this->propagatedUrlParameters as $parameter) {
				if (isset($_REQUEST[$parameter])) {
					$params[$parameter] = \get($parameter);
				}
			}
		}
		
		if (!empty($params)) {
			if (strpos($link, "?") === FALSE) {
				$link .= "?";
			} else {
				$link .= "&";
			}
			$paramsAsStrArray = array();
			foreach ($params as $key=>$value) {
				$paramsAsStrArray[] = urlencode($key).'='.urlencode($value);
			}
			$link .= implode("&", $paramsAsStrArray);
		}
		
		return $link;
	}
	
	private function getLinkWithoutParams() {
		if (strpos($this->url, "/") === 0
			|| strpos($this->url, "javascript:") === 0
			|| strpos($this->url, "http://") === 0
			|| strpos($this->url, "https://") === 0) {
			return $this->url;	
		}
		
		return ROOT_URL.$this->url;
	}
	
	/**
	 * If the URL of the current page matches the URL of the link, the link will be considered as "active".
	 * 
	 * @Property
	 * @param bool $activateBasedOnUrl
	 */
	public function setActivateBasedOnUrl($activateBasedOnUrl = true) {
		$this->activateBasedOnUrl = $activateBasedOnUrl;
		return $this;
	}
}
?>