File_Iterator 1.3
=================

This is the list of changes for the File_Iterator 1.3 release series.

File_Iterator 1.3.4
-------------------

* Symlinks are now followed.

File_Iterator 1.3.3
-------------------

* No changes.

File_Iterator 1.3.2
-------------------

* No changes.

File_Iterator 1.3.1
-------------------

* Fixed infinite loop in `File_Iterator_Facade::getCommonPath()` for empty directories.

File_Iterator 1.3.0
-------------------

* Added `File_Iterator_Facade` for the most common use case.
* Moved `File_Iterator_Factory::getFilesAsArray()` to `File_Iterator_Facade::getFilesAsArray()`.
* `File_Iterator_Factory` is no longer static.
