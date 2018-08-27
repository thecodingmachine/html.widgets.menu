<?php
/*
 * Copyright (c) 2012 David Negrier
 *
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Html\Widgets\Menu;

/**
 * Classes implementing the MenuInterface represent a whole menu (full of menu items).
 * It is important to note that a menu does not render directly in HTML (it has no toHtml method).
 * Instead, you must use another class (a Menu renderer class) to display the menu.
 * Usually, menu renderers are embedded into templates.
 *
 * @author david
 *
 */
interface MenuInterface
{
    
    /**
     * Returns a list of children elements for the menu (if there are some).
     * @return array<MenuItemInterface>
     */
    public function getChildren(): array;
    
    /**
     * If this function returns true, the menu item should not be displayed.
     *
     * @return bool
     */
    public function isHidden(): bool;
}
