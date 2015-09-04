# Component

**Component** library provides feature for components (modules/themes/languages/libraries) to load from any
custom directory like vendor.
* Modules should be registered using ModuleRegistrar::getInstance()->register()
* Themes should be registered using ThemeRegistrar::getInstance()->register()
* Languages should be registered using LanguageRegistrar::getInstance()->register()
* Libraries should be registered using LibrariesRegistrar::getInstance()->register()
