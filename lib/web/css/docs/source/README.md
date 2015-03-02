# Magento UI library

The Magento UI library is a flexible modular Magento frontend library that is designed to assist Magento theme developers. It employs a set of mixins for base elements to ease frontend theme development and customization. The Magento UI library offers the following characteristics for those who develop or customize Magento themes. It is:

* Built on LESS preprocessor
* Focused on web standards
* Customizable
* Easy to maintain
* Responsive
* Accessible

The library provides the ability to customize all of the following user interface elements:

* actions-toolbar
* breadcrumbs
* buttons
* drop-downs
* forms
* icons
* layout
* loaders
* messages
* pagination
* popups
* ratings
* resets
* responsive
* sections -  tabs and accordions
* tables
* tooltips
* typography
* utilities
* list of theme variables

# Magento UI library file structure
Magento UI library is located under `/lib/web/` folder. It and employs:
* `css/` folder where the library files are placed
* `fonts/` folder where default and icon fonts are placed
* `images/` folder where default images are placed
* `jquery/` folder where jQuery and jQuery widgets are placed

###Magento UI library structure

```css
/lib/web/
    ├── css/
    │    ├── docs/ (Library documentation)
    │    │    ├── source/
    │    │    │    ├── actions-toolbar.less
    │    │    │    ├── breadcrumbs.less
    │    │    │    ├── buttons.less
    │    │    │    ├── docs.less
    │    │    │    ├── dropdowns.less
    │    │    │    ├── forms.less
    │    │    │    ├── icons.less
    │    │    │    ├── layout.less
    │    │    │    ├── lib.less
    │    │    │    ├── loaders.less
    │    │    │    ├── messages.less
    │    │    │    ├── pages.less
    │    │    │    ├── popups.less
    │    │    │    ├── rating.less
    │    │    │    ├── resets.less
    │    │    │    ├── responsive.less
    │    │    │    ├── sections.less
    │    │    │    ├── tables.less
    │    │    │    ├── tooltips.less
    │    │    │    ├── typography.less
    │    │    │    ├── utilities.less
    │    │    │    ├── variables.less
    │    │    │    └── README.md
    │    │    ├─── extends.html
    │    │    ├─── actions-toolbar.html
    │    │    ├─── breadcrumbs.html
    │    │    ├─── buttons.html
    │    │    ├─── docs.css
    │    │    ├─── docs.html
    │    │    ├─── dropdowns.html
    │    │    ├─── forms.html
    │    │    ├─── icons.html
    │    │    ├─── index.html
    │    │    ├─── layout.html
    │    │    ├─── lib.html
    │    │    ├─── loaders.html
    │    │    ├─── messages.html
    │    │    ├─── pages.html
    │    │    ├─── popups.html
    │    │    ├─── rating.html
    │    │    ├─── resets.html
    │    │    ├─── responsive.html
    │    │    ├─── sections.html
    │    │    ├─── tables.html
    │    │    ├─── tooltips.html
    │    │    ├─── typography.html
    │    │    ├─── utilities.html
    │    │    └─── variables.html
    │    ├── source/
    │    │    ├── lib/ (Library source files)
    │    │    │    ├── _extends.less
    │    │    │    ├── _actions-toolbar.less
    │    │    │    ├── _breadcrumbs.less
    │    │    │    ├── _buttons.less
    │    │    │    ├── _dropdowns.less
    │    │    │    ├── _forms.less
    │    │    │    ├── _icons.less
    │    │    │    ├── _layout.less
    │    │    │    ├── _lib.less
    │    │    │    ├── _loaders.less
    │    │    │    ├── _messages.less
    │    │    │    ├── _navigation.less
    │    │    │    ├── _pages.less
    │    │    │    ├── _popups.less
    │    │    │    ├── _rating.less
    │    │    │    ├── _resets.less
    │    │    │    ├── _responsive.less
    │    │    │    ├── _sections.less
    │    │    │    ├── _tables.less
    │    │    │    ├── _tooltips.less
    │    │    │    ├── _typography.less
    │    │    │    ├── _utilities.less
    │    │    │    └── _variables.less
    │    │    ├── _extend.less
    │    │    └── _theme.less
    │    └── styles.less
    ├── fonts/
    │    └── Blank-Theme-Icons/ (Library custom icons font)
    ├── images/
    │    └── blank-theme-icons.png (Library icons sprite)
    └── jquery/ (Library javascript files)

```
&nbsp;

# Magento UI library naming convention

Magento UI library employs a variables and mixins naming convention whereby names of variables and mixins describe their purpose or functions they perform.

A variable or a mixins name may only contain lowercase alphanumeric characters, "-" and "_" characters. A variable name must start with "@" character.

If a variable or mixins name consists of more than one word, they must be concatenated with one hyphen character.

# Less variables naming

A *.less file variable can contain lowercase letters, numbers, special characters: "@", "-" and "_". It must start with "@" character and consist of words concatenated with one hyphen. It should not contain capital letters. A variable name should describe its purpose.

A variable name should be formed according to the following rule:

`'@' + 'object' + '-' + 'property' + '-' + 'state' = @object-property-state`

If it is a private variable (is used only in a mixin), it must start with "_" character after "@":

`'@' + '_' + 'object' + '-' + 'property' + '-' + 'state' = @_object-property-state`

#### Examples:

##### Acceptable:

```css
    @link-color-hover;
    @color-primary;
    @color-2;
```

Private variables:

```css
    @_padding-left;
    @_font-size;
```

##### Unacceptable:

```css
    @Link-Color-Hover;
    @colorPrimary;
    @color--primary;
    @paddingleft;
    @__font-size;
```
&nbsp;

# Less mixins naming

A less mixin name can contain lowercase letters, numbers, "-" and "_" characters. It should not contain capital letters.

A mixin name can consist of one or several words, concatenated with one hyphen. If the mixin is private, its name must start with the "_" character. Mixin should be named after property or action it describes.

#### Examples:

##### Acceptable:
```css
    .mixin-name() {}
    .transition() {}
    .mixin() {}
    ._button-gradient() {}
```
##### Unacceptable:
```css
    .mixinName() {}
    .__transition() {}
    .MiXiN() {}
    ._button--gradient() {}
```
&nbsp;

# Magento UI library code style

Magento UI library involves 3 comments levels:

First level comment must have an empty line before it and must be followed by an empty line:

```css
//
//  First level comment
//  _____________________________________________
```

Second level comment must have an empty line before it and must be followed by an empty line:

```css
//
//  Second level comment
//  ---------------------------------------------
```

Third level comment is recommended to have an empty line before it, when it is a single line comment. Also it is possible to use it in the end of a code line:

```css
// Third level comment
```

Magento UI library involves the following code style:

* Every CSS/LESS code line has a ";" character
* All selectors are set to lowercase
* The CSS/LESS file has a line break at EOF
* There is a space but not a line break before the opening brace "{" and a line brake after it
* There is a line break before the closing brace "}"
* The leading zeroes are removed (i.e. padding: .5em; instead of padding: 0.5em;)
* There is a line break between declarations
* There is a line break after selector delimiter
* Units in zero-valued dimensions are removed
* Single quotes are used
* Properties are sorted alphabetically
* All colors are set to lowercase
* Shorthands for hexadecimal colors are used
* The code has a 4 spaces indent
