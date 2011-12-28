@echo off

if "%PHPBIN%" == "" set PHPBIN=%PHP_PEAR_PHP_BIN%
if not exist "%PHPBIN%" for %%i in (php.exe) do set PHPBIN=%%~$PATH:i

"%PHPBIN%" batch.php
