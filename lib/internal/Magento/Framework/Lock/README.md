# Lock library

Lock library provides mechanism to acquire Magento system-wide lock. Default implementation is based on MySQL locks, where any locks are automatically released on connection close.

The library provides interface *LockManagerInterface* which provides following methods:

* *lock* - Acquires a named lock
* *unlock* - Releases a named lock
* *isLocked* - Tests if a named lock exists
