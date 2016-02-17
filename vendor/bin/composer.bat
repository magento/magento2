@ECHO OFF
SET BIN_TARGET=%~dp0/../composer/composer/bin/composer
php "%BIN_TARGET%" %*
