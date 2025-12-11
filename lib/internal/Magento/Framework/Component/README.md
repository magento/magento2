# Component

**Component** library provides feature for components (modules/themes/languages/libraries) to load from any
custom directory like vendor.

* Modules should be registered using

```php
ComponentRegistrar::register(ComponentRegistrar::MODULE, '<module name>', __DIR__);
```

* Themes should be registered using

```php
ComponentRegistrar::register(ComponentRegistrar::THEME, '<theme name>', __DIR__);
```

* Languages should be registered using

```php
ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, '<language name>', __DIR__);
```

* Libraries should be registered using

```php
ComponentRegistrar::register(ComponentRegistrar::LIBRARY, '<library name>', __DIR__);
```
