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
* components
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

### Magento UI library structure

```css
/lib/web/
    ├── css/
    │    ├── docs/ (Library documentation)
    │    │    ├── source/
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
    │    │    │    ├── _variables.less
    │    │    │    ├── _components.less
    │    │    │    ├── docs.less
    │    │    │    └── README.md
    │    │    ├─── extends.html
    │    │    ├─── actions-toolbar.html
    │    │    ├─── breadcrumbs.html
    │    │    ├─── buttons.html
    │    │    ├─── components.html
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
    │    └── source/
    │          ├── components/ (Reusable components files)
    │          │    └── _modals.less
    │          ├── lib/ (Library source files)
    │          │    └── variables/ (Decoupled variables)
    │          │          └── ...
    │          │    ├── _extends.less
    │          │    ├── _actions-toolbar.less
    │          │    ├── _breadcrumbs.less
    │          │    ├── _buttons.less
    │          │    ├── _dropdowns.less
    │          │    ├── _forms.less
    │          │    ├── _icons.less
    │          │    ├── _layout.less
    │          │    ├── _lib.less
    │          │    ├── _loaders.less
    │          │    ├── _messages.less
    │          │    ├── _navigation.less
    │          │    ├── _pages.less
    │          │    ├── _popups.less
    │          │    ├── _rating.less
    │          │    ├── _resets.less
    │          │    ├── _responsive.less
    │          │    ├── _sections.less
    │          │    ├── _tables.less
    │          │    ├── _tooltips.less
    │          │    ├── _typography.less
    │          │    ├── _utilities.less
    │          │    └── _variables.less
    │          ├── _extend.less
    │          └── _theme.less
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

# Less Code Standards

## General rules

### Indentation

Please verified that you use spaces instead tabs:

* Tab size: 4 spaces
* Indent size: 4 spaces
* Continuation indent: 4 spaces

```css
    .nav {
        .nav-item {
            ...
        }
    }
```

### Format

#### Braces

Add space before opening brace and line break after. And line break before closing brace.

##### Not recommended:
```css
    .nav{color: @nav__color;}
```

##### Recommended:

```css
    .nav {
        color: @nav__color;
    }
```

#### Selector delimiters

Add line break after each selector delimiter. Delimeter shouldn't have spaces before and after.

##### Not recommended:

```css
    .nav, .bar {
        color: @color__base;
    }
```

##### Recommended:

```css
    .nav,
    .bar {
        color: @color__base;
    }
```

#### Quotes

Use single quotes

##### Not recommended:

```css
    .nav {
        content: "lorem ipsum";
    }
```

##### Recommended:

```css
    .nav {
        content: 'lorem ipsum';
    }
```

#### Combinator indents

Use spaces before and after combinators

##### Not recommended:

```css
    .nav+.bar {
        color: @bar__color;
    }

    .nav +.bar {
        color: @bar__color;
    }

    .nav+ .bar {
        color: @bar__color;
    }
```

##### Recommended:

```css
    .nav + .bar {
        color: @bar__color;
    }
```

#### Properties line break

Use line break for each property declaration

##### Not recommended:

```css
    .nav {
        color: @nav__color; background-color: @nav__background-color;
    }
```

##### Recommended:

```css
    .nav {
        background-color: @nav__background-color;
        color: @nav__color;
    }
```

#### Properties colon indents

Use no space before property colon, and space after

##### Not recommended:

```css
    .nav {
        color : @nav__color;
    }

    .bar {
        color:@bar__color;
    }

    .item {
        color :@item__color;
    }
```

##### Recommended:

```css
    .nav {
        color: @nav__color;
    }
```

#### End of file

Each less file should be finished with new line

#### End of the selector

Each selector should be finished with new line

##### Not recommended:

```css
    .nav {
        background-color: @nav__background-color;
    }
    .bar {
        background-color: @bar__background-color;
    }
```

##### Recommended:

```css
    .nav {
        background-color: @nav__background-color;
    }

    .bar {
        background-color: @bar__background-color;
    }
```

#### End of the property line

Each property should be finished with semicolon

##### Not recommended:

```css
    .nav {
        background-color: @nav__background-color
    }
```

##### Recommended:

```css
    .nav {
        background-color: @nav__background-color;
    }
```

#### !Important property

Basically avoid use this property at all, but if you really really need it use the following format:

##### Not recommended:

```css
    .jquery-ui-calendar-item {
        background-color: @nav__background-color!important;
    }
```

##### Recommended:

```css
    .jquery-ui-calendar-item {
        background-color: @nav__background-color !important;
    }
```

### Comments

Please follow the next format of comments

```css
    //
    //  First level comment
    //  _____________________________________________

    .nav {
        background-color: @nav__background-color;
    }

    //
    //  Second level comment
    //  ---------------------------------------------

    .nav {
        background-color: @nav__background-color;
    }

    //  Comment
    .nav {
        //  New line comment
        background-color: @nav__background-color; // ToDo UI: todo inline comment
        color: @nav__color; // inline comment
    }
```

### Selectors

#### Types

According to browser support standards the oldest browser that we support is IE9+, it means that you can feel free to use almost all CSS3 selectors: descendants, attributes, pseudo classes, structural, pseudo elements, etc.
Exeption: Please avoid to use id selector.

##### Not recommended:

```css
    #foo {
        ...
    }
```

##### Recommended:

```css
    .nav {
        ...
    }

    .nav + bar {
        ...
    }

    .nav:not(.bar) {
        ...
    }
```

### Naming

#### Standard classes

All classes should be written in lowercase, starts with letter (exept helpers), words in classes should be separated with dash (minus sign '-')

##### Not recommended:

```css
    .navBar {
       ...
    }
```

##### Not recommended: underscore separation

```css
    .nav_bar {
       ...
    }
```

##### Recommended:

```css
    .nav-bar {
        ...
    }
```

#### Helper classes

Helper classes should be written in lowercase, starts with underscore ("_")

##### Example:

```css
    ._active {
        ...
    }
```

### Size

Use class names that are as short as possible but as long as necessary.
Try to convey what class is about while being as brief as possible.
Using class names this way contributes to acceptable levels of understandability and code efficiency.

##### Not recommended: too long

```css

    .navigation-panel-in-footer {
       ...
    }
```

##### Not recommended: too short

```css
    .nvpf {
       ...
    }
```

##### Recommended:

```css
    .nav-bar {
        ...
    }
```

### Writing

Write selector name together in single line, don't use concatenation

##### Not recommended:
```css
    .product {
        ...
        &-list {
            ...
            &-item {
                ...
            }
        }
    }
```

##### Recommended:

```css
    .product-list-item {
        ...
    }
```

### Meaning

**Use meaningful or generic class names.**

Instead of presentational or cryptic names, always use class names that reflect the purpose of the element in question, or that are otherwise generic.
Names that are specific and reflect the purpose of the element should be preferred as these are most understandable and the least likely to change.
Generic names are simply a fallback for elements that have no particular or no meaning different from their siblings. They are typically needed as “helpers.”
Using functional or generic names reduces the probability of unnecessary document or template changes.

##### Not recommended:
```css
    .foo-1901 {
        ...
    }
```

##### Not recommended: presentational

```css
    .button-green {
       ...
    }

    .clear {
       ...
    }
```

##### Recommended: specific

```css
    .category {
        ...
    }
    .category-title {
        ...
    }
```

### Type selectors

**Avoid qualifying class names with type selectors.**

Unless necessary (for example with helper classes), do not use element names in conjunction with IDs or classes.
Avoiding unnecessary ancestor selectors is useful for performance reasons.

##### Not recommended:

```css
    div.error {
       ...
    }
```

##### Recommended:

```css
    .error {
        ...
    }
```

Use type selectors in lowercase

##### Not recommended:

```css
    .nav > LI {
       ...
    }
```

##### Recommended:

```css
    .nav > li {
        ...
    }
```

### Nesting

Be careful with selectors nesting. In general try to use 3 nested levels as max.
Exception are pseudo elements and states

##### Not recommended:

```css
    .footer {
        ...
        .nav {
            ...
            .nav-list {
                ...
                .nav-list-item {
                    ...
                }
            }
        }
    }
```

##### Recommended:

```css
    .footer {
        ...
        .nav {
            ...
        }
        .nav-list {
            ...
        }
        .nav-list-item {
            ...
         }
    }
```

## Properties

### Sort

Sort all properties alphabetical. Mixins, variables etc. should go first

##### Not recommended:

```css
    .nav {
        color: @nav__color;
        text-align: center;
        background-color: @nav__background-color;
    }
```

##### Recommended:

```css
    .nav {
        background-color: @nav__background-color;
        color: @nav__color;
        text-align: center;
    }
```

### Shorthand

Use shortland properties where possible.

CSS offers a variety of shorthand properties (like font) that should be used whenever possible, even in cases where only one value is explicitly set.
Using shorthand properties is useful for code efficiency and understandability.

##### Not recommended:

```css
    border-top-style: none;
    padding-bottom: 2rem;
    padding-left: 1rem;
    padding-right: 1rem;
    padding-top: 0;
```

 ##### Recommended:

```css
    border-top: 0;
    padding: 0 1em 2em;
```

### 0 and units

Omit unit specification after "0" values.

##### Not recommended:

```css
    border-width: 0px;
    margin: 0rem;
```

##### Recommended:

```css
    border-width: 0;
    margin: 0;
```

### Floating values

Omit leading "0"s in values, use dot instead

##### Not recommended:

```css
    margin-left: 0.5rem;
```

##### Recommended:

```css
    margin-left: .5rem;
```

### Hexadecimal notation

* Use lowercase only
* Use 3 character hexadecimal notation where possible.
* For color values that permit it, 3 character hexadecimal notation is shorter and more succinct.
* Also please avoid hex color in properties, use only variables instead.

##### Not recommended:

```css
    color: #ff0000;
    @nav__color: #FAFAFA;
    @nav-item__color: red;
```

##### Recommended:

```css
    @nav__color: #fafafa;
    @nav-item__color: #f00;
    ...
    color: @nav-item__color;
```

## Variables

### Location

#### Local variables

If variables are local and used only in module scope it should be located in module file, on the top of the file with general comment

Example **_module.less**:
```css
    ...

    //
    //  Variables
    //  _____________________________________________

    //  Colors
    @btn__color: @color-brownie;
    @btn-primary__color: @color-white;
    @btn-secondary__color: @color-white;
    ...
```

#### Theme variables

If variables are common for couple modules it should be located in **_theme.less** file

#### Global variables

If variables are common for couple themes it should be located in global lib in **_variables.less** file

### Naming

All variables should be written in lowercase.

#### Value variables

##### General model is:

```css
    @property-name
```

##### Examples:

```css
    @primary__color: @color-phoenix;
    @indent__base: 2rem;
    @border-radius-round: 100%;
```

#### Parameter variables

##### General model is:

```css
    @component-element__state__property__modifier
```

Please mention that component can be not only element name, it also can be primary, secondary, tertiary
Base is a modifier

##### Examples:

```css
    @color-orange: '';
    @link__hover__color: '';
    @nav-element__background-color: '';
    @secondary__color: '';
    @side-nav__indent__s: '';
    @side-nav-el__background-color: '';
    @side-nav-el__active__background-color: '';
    @side-nav-el__active-focus__background-color: '';
    @side-nav-el__active-focus__font-size__xl: '';
    @text__color__base: '';
```

## Mixins

### Location

Theme mixins (except extends) should be saved in **source/utilities** folder
Extends that used in more than one theme should be saved in lib **lib/source/utilities.less** (will be separated into utilities folder)

### Naming

In name creation you can follow the same rules as for the creation of class names.
For mixins grouping use prefix__

##### Example:

```css
    .extend__clearfix (...) {
        ...
    }

    .vendor-prefix__flex-direction (...) {
        ...
    }
```

## Extends

### Location

Local extends that used only in one file, should be written in this **local file**.
Extends that used in more than two files should be saved in theme **source/_extend.less**
Extends that used in more than one theme should be saved in lib **lib/source/_abstract.less** (will be renamed to _extend.less)

### Naming

Extend class names should have prefix **.abs-** (from abstract)
