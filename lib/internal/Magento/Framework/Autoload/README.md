# Autoload

**Autoload** library contains an abstract wrapper for Composer's generated autoloader.

* AutoloaderInterface provides abstract ability use and modify the autoloader class.
* AutoloaderRegistry allows the same instance of the autoloader to put accessed across the code base.
* ClassLoaderWrapper wraps around Composer's generated autoloader in order to insulate it.
* Populator fills in PSR-0 and PSR-4 standard namespace-directory mappings for the autoloader.
