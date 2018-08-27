<?php
/*
 * Copyright (c) 2012 David Negrier
 * 
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Html\Widgets\Menu;

use Mouf\Utils\Common\ConditionInterface\ConditionInterface;
use Mouf\Utils\I18n\Fine\Translate\LanguageTranslationInterface;
use Mouf\Html\HtmlElement\HtmlElementInterface;
use Mouf\Html\Renderer\Renderable;
use Mouf\Utils\I18n\Fine\TranslatorInterface;

/**
 * This class represent a menu item.
 * It is important to note that a menu item does not render directly in HTML (it has no toHtml method).
 * Instead, you must use another class (a Menu renderer class) to display the menu.
 * Usually, menu renderers are embedded into templates.
 * 
 */
class MenuItem implements MenuItemInterface, HtmlElementInterface {
	use Renderable;
	
	/**
	 * The text for the menu item
	 *
	 * @var string
	 */
	private $label;
	
	/**
	 * The link for the menu (relative to the root url), unless it starts with / or http:// or https:// or # or ?.
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
	 * @var TranslatorInterface|null
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
     * @var string
     */
    private $rootUrl;

    /**
	 * Constructor.
	 *
	 * @Important
	 * @param string $label The text for the menu item
	 * @param string $url The link for the menu (relative to the root url), unless it starts with / or http:// or https:// or # or ?.
	 * @param array<MenuItemInterface> $children
	 */
	public function __construct(string $label=null, string $url=null, array $children=[], string $rootUrl = '/') {
		$this->label = $label;
		$this->url = $url;
		$this->children = $children;
        $this->rootUrl = $rootUrl;
    }

	/**
	 * Returns the label for the menu item.
	 * @return string
	 */
	public function getLabel(): string {
		if ($this->translationService) {
			return $this->translationService->getTranslation($this->label);
		} else {
			return $this->label;
		}
	}
	
	/**
	 * The label for this menu item.
	 * 
	 * @param string $label
	 */
	public function setLabel(string $label): void {
		$this->label = $label;
	}
	
	/**
	 * Returns the URL for this menu (or null if this menu is not a link).
	 * @return string
	 */
	public function getUrl(): string {
		return $this->url;
	}
	
	/**
	 * The link for the menu (relative to the root url), unless it starts with / or http:// or https://.
	 *
	 * @param string|null $url
	 */
	public function setUrl(?string $url): self {
		$this->url = $url;
		return $this;
	}	
	
	/**
	 * Returns a list of children elements for the menu (if there are some).
	 * 
	 * @see MenuItemInterface::getChildren()
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
	
	private $sorted = false;
	
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
	 * @param array<MenuItemInterface> $children
	 */
	public function setChildren(array $children): self {
		$this->sorted = false;
		$this->children = $children;
		return $this;
	}

	/**
	 * Adds a menu item as a child of this menu item.
	 * 
	 * @param MenuItemInterface $menuItem
	 */
	public function addMenuItem(MenuItemInterface $menuItem): void {
		$this->sorted = false;
		$this->children[] = $menuItem;
	}
	
	/**
	 * Returns true if the menu is in active state (if we are on the page for this menu).
	 * @return bool
	 */
	public function isActive(): bool {
		if ($this->isActive) {
			return true;
		}
		// TODO: really compare URLs instead of performin a strpos.
		// We can do this using the parse_url function
		//var_dump(parse_url(ROOT_URL.$this->url));
		
		//$requestUrl = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
		
		//error_log($_SERVER['REQUEST_URI'].' - '.$requestUrl.' - '.ROOT_URL.$this->url);
		if($this->activateBasedOnUrl && $this->url !== null) {
			$urlParts = parse_url($_SERVER['REQUEST_URI']);
			$menuUrlParts = parse_url($this->getLinkWithoutParams());
			
			if (isset($menuUrlParts['path'])) {
				$menuUrl = $menuUrlParts['path'];
			} else {
				$menuUrl = '/';
			}
			
			if (isset($urlParts['path'])) {
				$requestUrl = $urlParts['path'];
			} else {
				$requestUrl = '/';
			}
			
			if($requestUrl == $menuUrl) {
				return true;
			}
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
	 * @param bool $isActive
	 */
	public function setIsActive(bool $isActive): self {
		$this->isActive = $isActive;
		return $this;
	}
	
	/**
	 * Enables the menu item (activates it).
	 * 
	 */
	public function enable(): self {
		$this->isActive = true;
		return $this;
	}
	
	/**
	 * Returns true if the menu should be in extended state (if we can see the children directly).
	 * @return bool|null
	 */
	public function isExtended(): ?bool {
		return $this->isExtended;
	}

	/**
	 * Whether the menu is extended or not.
	 * This should not have an effect if the menu has no child.
	 * 
	 * @param bool $isExtended
	 */
	public function setIsExtended(bool $isExtended = true): self {
		$this->isExtended = $isExtended;
		return $this;
	}
	
	/**
	 * Returns an optional CSS class to apply to the menu item.
	 * @return string
	 */
	public function getCssClass(): string {
		return $this->cssClass;
	}

	/**
	 * An optional CSS class to apply to the menu item.
	 * Use of this property depends on the menu implementation.
	 * 
	 * @param string $cssClass
	 */
	public function setCssClass(string $cssClass): self {
		$this->cssClass = $cssClass;
		return $this;
	}

	/**
	 * Level of priority used to order the menu items.
	 * 
	 * @param float|null $priority
	 */
	public function setPriority(?float $priority): self {
		$this->priority = $priority;
		return $this;
	}

	/**
	 * Returns the level of priority. It is used to order the menu items.
	 * @return float
	 */
	public function getPriority(): ?float {
		return $this->priority;
	}

	/**
	 * Returns the list of additionnal style
	 * @return array<MenuItemStyleInterface>
	 */
	public function getAdditionalStyles(): array {
		return $this->additionalStyles;
	}

	/**
	 * Returns the one of additionnal style
	 * @return MenuItemStyleInterface
	 */
	public function getAdditionalStyleByType(string $type): MenuItemStyleInterface {
		$return = null;
		if($this->additionalStyles) {
			foreach ($this->additionalStyles as $additionalStyle) {
				if($additionalStyle instanceof $type) {
					if($return === null)
						$return = $additionalStyle;
					else
						throw new \LogicException("MenuItem: There are many instance of $type, please use getAdditionalStylesByType function to get all instance");
				}
			}
		}
		return $return;
	}

	/**
	 * Returns the list of additionnal style
	 * @return array<MenuItemStyleInterface>
	 */
	public function getAdditionalStylesByType(string $type): array {
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
	 * @param array<MenuItemStyleInterface> $menuItemStyleInterface
	 */
	public function setAdditionalStyles(array $menuItemStyleInterface): self {
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
	public function isSeparator(): bool {
		return false;
	}
	
	/**
	 * If this function returns true, the menu item should not be displayed.
	 * 
	 * @return bool
	 */
	public function isHidden(): bool {
		if ($this->displayCondition === null) {
			return false;
		}
		return !$this->displayCondition->isOk();
	}

	/**
	 * If any translation service is set, it will be used to translate the label.
	 * Otherwise, the label is displayed "as-is".
	 * 
	 * @param TranslatorInterface $translationService
	 */
	public function setTranslationService(TranslatorInterface $translationService): self {
		$this->translationService = $translationService;
		return $this;
	}


	/**
	 * If set, this display condition is tested. If it returns false, the menu will be hidden.
	 * 
	 * @param ConditionInterface $displayCondition
	 */
	public function setDisplayCondition(ConditionInterface $displayCondition): self {
		$this->displayCondition = $displayCondition;
		return $this;
	}	
	
	/**
	 * A list of parameters that are propagated by the link.
	 * For instance, if the parameter "mode" is set to 42 on the page (because the URL is http://mywebsite/myurl?mode=42),
	 * then if you choose to propagate the "mode" parameter, the menu link will have "mode=42" as a parameter.
	 *
	 * @param array<string> $propagatedUrlParameters
	 */
	public function setPropagatedUrlParameters(array $propagatedUrlParameters): self {
		$this->propagatedUrlParameters = $propagatedUrlParameters;
		return $this;
	}
	
	
	/**
	 * Returns the absolute URL, with parameters if required.
	 * @return string
	 */
	public function getLink(): ?string {
		if ($this->url === null) {
			return null;
		}
		$link = $this->getLinkWithoutParams();
		
		$params = array();
		// First, get the list of all parameters to be propagated
		if (is_array($this->propagatedUrlParameters)) {
			foreach ($this->propagatedUrlParameters as $parameter) {
				if (isset($_REQUEST[$parameter])) {
					$params[$parameter] = $_REQUEST($parameter);
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
	
	private function getLinkWithoutParams(): string {
		if (strpos($this->url, "/") === 0
			|| strpos($this->url, "javascript:") === 0
			|| strpos($this->url, "http://") === 0
			|| strpos($this->url, "https://") === 0
			|| strpos($this->url, "?") === 0
			|| strpos($this->url, "#") === 0) {
			return $this->url;	
		}
		
		return $this->rootUrl.$this->url;
	}
	
	/**
	 * If the URL of the current page matches the URL of the link, the link will be considered as "active".
	 * 
	 * @param bool $activateBasedOnUrl
	 */
	public function setActivateBasedOnUrl(bool $activateBasedOnUrl = true): self {
		$this->activateBasedOnUrl = $activateBasedOnUrl;
		return $this;
	}
}
