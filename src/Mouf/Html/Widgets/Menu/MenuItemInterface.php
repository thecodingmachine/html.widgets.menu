<?php
/*
 * Copyright (c) 2012 David Negrier
 *
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Html\Widgets\Menu;

/**
 * Classes implementing the MenuItemInterface represent a menu item.
 * It is important to note that a menu item does not render directly in HTML (it has no toHtml method).
 * Instead, you must use another class (a Menu renderer class) to display the menu.
 * Usually, menu renderers are embedded into templates.
 *
 * @author david
 *
 */
interface MenuItemInterface /*extends MenuInterface*/
{
    
    /**
     * Returns the label for the menu item.
     * @return string
     */
    public function getLabel(): string;

    /**
     * Returns the URL for this menu (or null if this menu is not a link).
     * @return string
     */
    public function getUrl(): ?string;
    
    /**
     * Returns a list of children elements for the menu (if there are some).
     * @return array<MenuItemInterface>
     */
    public function getChildren(): array;
    
    /**
     * Returns true if the menu is in active state (if we are on the page for this menu).
     * @return bool
     */
    public function isActive(): bool;
    
    /**
     * Returns true if the menu should be in extended state (if we can see the children directly).
     * @return bool
     */
    public function isExtended(): ?bool;
    
    /**
     * Returns an optional CSS class to apply to the menu item.
     * @return string
     */
    public function getCssClass(): string;
    
    /**
     * Returns true if this menu item is a separator.
     * A separator is a special case of menu item that is juste here to separate menu items with a bar.
     * It has no label, no URL, etc...
     *
     * @return bool
     */
    public function isSeparator(): bool;
    
    /**
     * If this function returns true, the menu item should not be displayed.
     *
     * @return bool
     */
    public function isHidden(): bool;
    
    /**
     * Returns the level of priority. It is used to order the menu items.
     * @return float|null
     */
    public function getPriority(): ?float;
    
    /**
     * Returns the list of additionnal style
     * @return array<MenuItemStyleInterface>
     */
    public function getAdditionalStyles(): array;
}
