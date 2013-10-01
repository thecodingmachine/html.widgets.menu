What is this package?
=====================

This package contains base objects and interfaces to describe menus and menu items.
Basically, using interfaces and objects of this package, you can describe a menu.

Menus are rendered using [Mouf's rendering system](http://mouf-php.com/packages/mouf/html.renderer/README.md).

Using the rendering system, other packages, or templates, or your project can override the HTML of the menu.

In practice
-----------

A menu is defined using the `Menu` class.
The `Menu` class can contain many `MenuItem`. Each menu item can contain many children `MenuItem`.

Mouf package
------------

This package is part of Mouf (http://mouf-php.com), an effort to ensure good developing practices by providing a graphical dependency injection framework.
Using Mouf's user interface, you can create your menu graphically, by creating instances of Menu and MenuItem.
