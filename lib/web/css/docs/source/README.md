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
* navigation
* pagination
* popups
* ratings
* tabs and accordions (sections)
* tables
* tooltips
* typography
* list of theme variables

# Magento UI library file structure
Magento UI library is located under `pub/lib/` folder. It and employs:
* `css/` folder where the library files are placed
* `fonts/` folder where default and  icon fonts are placed
* `images/` folder where default images are placed
* `jquery/` folder where jQuery and jQuery widgets are placed

###Magento UI library structure

```css
pub/lib/
    ├── css/
    │    ├── docs/ (Library documentation)
    │    │    ├── source/
    │    │    │    ├── abstract.less
    │    │    │    ├── actions-toolbar.less
    │    │    │    ├── breadcrumbs.less
    │    │    │    ├── buttons.less
    │    │    │    ├── docks.less
    │    │    │    ├── dropdowns.less
    │    │    │    ├── forms.less
    │    │    │    ├── icons.less
    │    │    │    ├── layout.less
    │    │    │    ├── lib.less
    │    │    │    ├── loaders.less
    │    │    │    ├── messages.less
    │    │    │    ├── navigation.less
    │    │    │    ├── pages.less
    │    │    │    ├── popups.less
    │    │    │    ├── rating.less
    │    │    │    ├── resets.less
    │    │    │    ├── sections.less
    │    │    │    ├── tables.less
    │    │    │    ├── tooltips.less
    │    │    │    ├── typography.less
    │    │    │    ├── utilities.less
    │    │    │    ├── variables.less
    │    │    │    └── README.md
    │    │    ├─── abstract.html
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
    │    │    ├─── navigation.html
    │    │    ├─── pages.html
    │    │    ├─── popups.html
    │    │    ├─── rating.html
    │    │    ├─── resets.html
    │    │    ├─── sections.html
    │    │    ├─── tables.html
    │    │    ├─── tooltips.html
    │    │    ├─── typography.html
    │    │    ├─── utilities.html
    │    │    └─── variables.html
    │    ├── source/
    │    │    ├── lib/ (Library source files)
    │    │    │    ├── abstract.less
    │    │    │    ├── actions-toolbar.less
    │    │    │    ├── breadcrumbs.less
    │    │    │    ├── buttons.less
    │    │    │    ├── dropdowns.less
    │    │    │    ├── forms.less
    │    │    │    ├── icons.less
    │    │    │    ├── layout.less
    │    │    │    ├── lib.less
    │    │    │    ├── loaders.less
    │    │    │    ├── messages.less
    │    │    │    ├── navigation.less
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
    │    │    │    └── variables.less
    │    │    └── theme.less
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

### Examples:

#### Acceptable:

`
    @link-color-hover;
    @color-primary;
    @color-2;
`

Private variables:

`  
    @_padding-left;
    @_font-size;
`

#### Unacceptable:

`
    @Link-Color-Hover;
    @colorPrimary;
    @color--primary;
    @paddingleft;
    @__font-size;
`

# Less mixins naming

A less mixin name can contain lowercase letters, numbers, "-" and "_" characters. It should not contain capital letters.

A mixin name can consist of one or several words, concatenated with one hyphen. If the mixin is private, its name must start with the "_" character. Mixin should be named after property or action it describes.

### Examples:

#### Acceptable:
`
    .mixin-name() {}
    .transition() {}
    .mixin() {}
    ._button-gradient() {}
`
#### Unacceptable:
`
    .mixinName() {}
    .__transition() {}
    .MiXiN() {}
    ._button--gradient() {}
`
