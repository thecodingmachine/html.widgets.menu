What is this package?
=====================

This package contains base objects and interfaces to describe menus and menu items.
Basically, using interfaces and objects of this package, you can describe a menu.

The menu concept is completely abstract. You won't find in this package a "toHtml" method that would render
the menu in HTML. Instead, you define a menu using the Menu and MenuItem objects, and you use a third-party
"MenuRenderer" to render the object in HTML.

Obviously, this package is useless on its own. It is useful only if you use a menu renderer.

Mouf package
------------

This package is part of Mouf (http://mouf-php.com), an effort to ensure good developing practices by providing a graphical dependency injection framework.
Using Mouf's user interface, you can create your menu graphically, by creating instances of Menu and MenuItem.

In practice
-----------

A menu is defined using the Menu class.
The Menu class can contain many MenuItem. Each menuitem can contain many MenuItem.
