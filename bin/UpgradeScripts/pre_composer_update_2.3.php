#!/usr/bin/php
<?php
/**
 * Script for updating Magento with 2.3 requirements that can't be done by composer update or setup:upgrade
 *
 * Steps included:
 *  - Require new version of the metapackage
 *  - Updating "require-dev" section
 *  - Add "Zend\\Mvc\\Controller\\": "setup/src/Zend/Mvc/Controller/" to composer.json "autoload":"psr-4" section
 *  - Updating Magento/Updater if it's installed
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$_scriptName = basename(__FILE__);

define(
    'SYNOPSIS',
<<<SYNOPSIS
Updates Magento with 2.3 requirements that can't be done by `composer update` or `bin/magento setup:upgrade`. 
This should be run prior to running `composer update` or `bin/magento setup:upgrade`.

Steps included:
 - Require new version of the metapackage
 - Updating "require-dev" section
 - Add "Zend\\Mvc\\Controller\\": "setup/src/Zend/Mvc/Controller/" to composer.json "autoload":"psr-4" section
 - Updating Magento/Updater if it's installed

Usage: php -f $_scriptName -- --root="</path/to/magento/root/>" [--composer=</path/to/composer/executable>] 
           [--edition=<community|enterprise>] [--version=<magento_package_version>] [--repo=<composer_repo_url>]
           [--help]

Required:
 --root="</path/to/magento/root/>"
    Path to the Magento installation root directory

Optional:
 --composer="</path/to/composer/executable>"
    Path to the composer executable
    - Default: The composer found in PATH
    
 --edition=<community|enterprise>
    The Magento edition to upgrade to.  Open Source = 'community', Commerce = 'enterprise'
    - Default: The edition currently required in composer.json
    
 --version=<magento_package_version>
    The Magento version to upgrade to
    - Default: The value for the "version" field in composer.json
    
 --repo=<composer_repo_url>
    The Magento composer repository to pull new packages from
    - Default: The Magento repository configured in composer.json

 --help
    Display this message
SYNOPSIS
);


$opts = getopt('', [
    'root:',
    'composer:',
    'edition:',
    'version:',
    'repo:',
    'help'
]);

if (isset($opts['help'])) {
    echo SYNOPSIS . PHP_EOL;
    exit(0);
}

try {
    if (empty($opts['root'])) {
        throw new BadMethodCallException("Magento root must be given with '--root'" . PHP_EOL . PHP_EOL . SYNOPSIS);
    }

    $rootDir = $opts['root'];
    if (!is_dir($rootDir)) {
        throw new InvalidArgumentException("Magento root directory '$rootDir' does not exist");
    }

    $cmd = (!empty($opts['composer']) ? $opts['composer'] : 'composer') . " --working-dir='$rootDir'";
    $jsonData = json_decode(file_get_contents("$rootDir/composer.json"), true);

    $version = !empty($opts['version']) ? $opts['version'] : $jsonData['version'];
    if (empty($version)) {
        throw new InvalidArgumentException('Value not found for "version" field in composer.json');
    }

    if (!empty($opts['edition'])) {
        $edition = $opts['edition'];
    }
    else {
        $editionRegex = '|^magento/product\-(?<edition>[a-z]+)\-edition$|';

        foreach (array_keys($jsonData["require"]) as $requiredPackage) {
            if (preg_match($editionRegex, $requiredPackage, $matches)) {
                $edition = $matches['edition'];
                break;
            }
        }
        if (empty($edition)) {
            throw new InvalidArgumentException('No valid Magento edition found in composer.json requirements');
        }
    }

    echo "Backing up $rootDir/composer.json" . PHP_EOL;
    copy("$rootDir/composer.json", "$rootDir/composer.json.bak");

    echo "Updating Magento product requirement to magento/product-$edition-edition=$version" . PHP_EOL;
    if ($edition == "enterprise") {
        execVerbose("$cmd remove --verbose magento/product-community-edition --no-update");
    }
    execVerbose("$cmd require --verbose magento/product-$edition-edition=$version --no-update");

    echo 'Updating "require-dev" section of composer.json' . PHP_EOL;
    execVerbose("$cmd require --dev --verbose " .
        "phpunit/phpunit:~6.2.0 " .
        "friendsofphp/php-cs-fixer:~2.10.1 " .
        "lusitanian/oauth:~0.8.10 " .
        "pdepend/pdepend:2.5.2 " .
        "sebastian/phpcpd:~3.0.0 " .
        "squizlabs/php_codesniffer:3.2.2 --no-update");

    execVerbose("$cmd remove --dev --verbose sjparkinson/static-review fabpot/php-cs-fixer --no-update");

    echo 'Adding "Zend\\\\Mvc\\\\Controller\\\\": "setup/src/Zend/Mvc/Controller/" to "autoload":"psr-4"' . PHP_EOL;
    $jsonData = json_decode(file_get_contents("$rootDir/composer.json"), true);
    $jsonData["autoload"]["psr-4"]["Zend\\Mvc\\Controller\\"] = "setup/src/Zend/Mvc/Controller/";

    $jsonData["version"] = $version;
    file_put_contents("$rootDir/composer.json", json_encode($jsonData, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

    if (file_exists("$rootDir/update")) {
        echo "Replacing Magento/Updater" . PHP_EOL;

        $mageUrls = [];
        if (isset($opts['repo'])) {
            $mageUrls[] = $opts['repo'];
        }
        else {
            $composerUrls = array_map(function ($r) { return $r["url"]; },
                    array_filter($jsonData['repositories']), function ($r) { return $r["type"] == "composer"; });
            $mageUrls = array_filter($composerUrls, function($u) { return strpos($u, ".mage") !== false; });

            if (count($mageUrls) == 0) {
                throw new InvalidArgumentException('No Magento composer repository urls found in composer.json');
            }
        }

        echo "Backing up $rootDir/update" . PHP_EOL;
        rename("$rootDir/update", "$rootDir/update.bak");
        $newPackage = "magento/project-$edition-edition=$version";
        foreach ($mageUrls as $repoUrl) {
            try {
                deleteFilepath("$rootDir/temp_update");
                execVerbose("$cmd create-project --repository=$repoUrl $newPackage $rootDir/temp_update --no-install");
                rename("$rootDir/temp_update/update", "$rootDir/update");
                echo "Upgraded Magento/Updater from magento/project-$edition-edition $version on $repoUrl" . PHP_EOL;
                unset($exception);
                break;
            }
            catch (Exception $e) {
                echo "Failed to find Magento package on $repoUrl" . PHP_EOL;
                $exception = $e;
            }
        }
        deleteFilepath("$rootDir/temp_update");

        if (isset($exception)) {
            throw $exception;
        }
    }
} catch (Exception $e) {
    if ($e->getPrevious()) {
        $message = (string)$e->getPrevious();
    } else {
        $message = $e->getMessage();
    }

    try {
        error_log($message . PHP_EOL . PHP_EOL . "Error encountered; resetting backups" . PHP_EOL);
        if (file_exists("$rootDir/update.bak")) {
            deleteFilepath("$rootDir/update_temp");
            deleteFilepath("$rootDir/update");
            rename("$rootDir/update.bak", "$rootDir/update");
        }

        if (file_exists("$rootDir/composer.json.bak")) {
            deleteFilepath("$rootDir/composer.json");
            rename("$rootDir/composer.json.bak", "$rootDir/composer.json");
        }
    }
    catch (Exception $e) {
        error_log($e->getMessage() . PHP_EOL);
    }

    exit($e->getCode() == 0 ? 1 : $e->getCode());
}

/**
 * Execute a command with automatic escaping of arguments
 *
 * @param string $command
 * @return array
 * @throws Exception
 */
function execVerbose($command)
{
    $args = func_get_args();
    $args = array_map('escapeshellarg', $args);
    $args[0] = $command;
    $command = call_user_func_array('sprintf', $args);
    echo $command . PHP_EOL;
    exec($command . " 2>&1", $output, $exitCode);
    $outputString = join(PHP_EOL, $output);
    if (0 !== $exitCode) {
        throw new Exception($outputString, $exitCode);
    }
    echo $outputString . PHP_EOL;
    return $output;
}

/**
 * Deletes a file or a directory and all its contents
 *
 * @param string $path
 * @throws Exception
 */
function deleteFilepath($path) {
    if (!file_exists($path)) {
        return;
    }
    if (is_dir($path)) {
        $files = array_diff(scandir($path), array('..', '.'));
        foreach ($files as $file) {
            deleteFilepath("$path/$file");
        }
        rmdir($path);
    }
    else {
        unlink($path);
    }
    if (file_exists($path)) {
        throw new Exception("Failed to delete $path");
    }
}
