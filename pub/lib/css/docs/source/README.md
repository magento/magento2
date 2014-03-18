# MageLess framework

MageLess framework is a is a flexible and modular Magento front-end framework, that is designed to assist Magento theme developer. It employs set of mixins for base elements to ease theme customization.

**MageLess:**
* Layouts
* Typography 
* Forms
* Tables
* Navigation
* Buttons
* Theme variables list
* Responsive
* Easy to maintain
* Customizable
* Focused on web standards & accessibility
* Build on LESS dynamic stylesheet language

# How to add a new theme

# How to customize magento theme

# How to extend magento theme

# Theme structure

# Framework file structure
MageLess framework is located in `pub/lib/` folder. It and employs:
* `css/` folder where the framework files are placed, 
* `fonts/` folder where default and  icon fonts are placed
* `images/` folder where default images are placed
* `jquery/` folder where jquery and jquery widgets are placed

MageLess framework structure:

```css
pub/lib/
    ├── css/
    │    ├── docs/ (Framework documentation)
    │    │    ├── source/
    │    │    │    ├── actions-toolbar.less
    │    │    │    ├── breadcrumbs.less
    │    │    │    ├── buttons.less
    │    │    │    ├── dropdowns.less
    │    │    │    ├── forms.less
    │    │    │    ├── icons.less
    │    │    │    ├── loaders.less
    │    │    │    ├── messages.less
    │    │    │    ├── page.less
    │    │    │    ├── pager.less
    │    │    │    ├── popups.less
    │    │    │    ├── rating.less
    │    │    │    ├── resets.less
    │    │    │    ├── sections.less
    │    │    │    ├── tables.less
    │    │    │    ├── tooltips.less
    │    │    │    ├── typography.less
    │    │    │    ├── utilities.less
    │    │    │    ├── vars.less
    │    │    │    └── README.md
    │    │    ├─── index.html
    │    │    ├─── docs.css
    │    │    └─── ...  _All other .html files generated with StyleDocco
    │    ├── source/
    │    │    ├── lib/ (Framework source files)
    │    │    │    ├── actions-toolbar.less
    │    │    │    ├── breadcrumbs.less
    │    │    │    ├── buttons.less
    │    │    │    ├── dropdowns.less
    │    │    │    ├── forms.less
    │    │    │    ├── icons.less
    │    │    │    ├── loaders.less
    │    │    │    ├── messages.less
    │    │    │    ├── page.less
    │    │    │    ├── pager.less
    │    │    │    ├── popups.less
    │    │    │    ├── rating.less
    │    │    │    ├── resets.less
    │    │    │    ├── sections.less
    │    │    │    ├── tables.less
    │    │    │    ├── tooltips.less
    │    │    │    ├── typography.less
    │    │    │    ├── utilities.less
    │    │    │    └── vars.less
    │    │    └── theme/ (Initial framework setup)
    │    │          ├── actions-toolbar.less
    │    │          ├── breadcrumbs.less
    │    │          ├── buttons.less
    │    │          ├── dropdowns.less
    │    │          ├── forms.less
    │    │          ├── icons.less
    │    │          ├── loaders.less
    │    │          ├── messages.less
    │    │          ├── page.less
    │    │          ├── pager.less
    │    │          ├── popups.less
    │    │          ├── rating.less
    │    │          ├── resets.less
    │    │          ├── sections.less
    │    │          ├── tables.less
    │    │          ├── tooltips.less
    │    │          ├── typography.less
    │    │          ├── utilities.less
    │    │          └── vars.less
    │    ├── tools/ (Tools for generating styles and documentation)
    │    │    └── compilation.sh
    │    ├── styles.less
    │    └── styles.css
    ├── fonts
    ├── images
    └── jquery
```
&nbsp;

# Framework naming convention

MageLess framework employs a variables and mixins naming convention whereby names of variables and mixins describe their purpose or functions they perform.

A variable or a mixins name may only contain lowercase alphanumeric characters, “-” and “_” symbols. A variable name must start with “@” symbol.

If a variable or mixins name consists of more than one word, they must be separated by one hyphen symbol.

# Less variables naming

A variable, used in *.less file, can contain lowercase letters, numbers, special symbols: "@", "-" and "_". It should start from "@" symbol and consist of words concatenated with one hyphen. It should not contain capital letters. A variable name should describe its purpose.

A variable name should be formed following the rule:

`'@' + 'object' + '-' + 'property' + '-' + 'state' = @object-property-state`

If it is a private variable (is used only in a mixin), it should start with "_" symbol after "@":

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

A less mixin name can contain lowercase letters, numbers, "-" and "_" symbols. It should not contain capital letters.

A mixin name can contain one or several words divided with one hyphen. If the mixin is private, its name must start from the "_" symbol. Mixin should be named after property or action it describes.

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
# Less extend - use carefully

A developer must use less extend carefully and must check resulting css after a new extend is used. This practice will help to avoid unnecessary styles for non-existing elements, that can be generated from an element, the extend is generated from.

# Less nesting - as short as possible

A developer should avoid deep less nesting. The tag nesting should not exceed 4 selectors.

# Class naming

# Example class naming

A class name for examples in documentation can contain lowercase letters, numbers and hyphen symbol. It should start from "example" keyword followed by object name and (not mandatory) an index number. A class name should consist of words concatenated with one hyphen. It should not contain capital letters.

A class name for examples in documentation should be formed following the rule:

`'example' + '-' + 'object' (+ '-' + 'number') = example-object(-number)`


#### Acceptable:

`
    .example-button-1
    .example-icons-3
    .example-popups
`

#### Unacceptable:

`
    .example-1
    .example
    .button-example-1
    .example-popups-
    .ExamplePages
`
