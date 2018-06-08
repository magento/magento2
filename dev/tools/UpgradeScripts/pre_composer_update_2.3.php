#!/usr/bin/php
<?php
/**
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
 - Update "require-dev" section
 - Add "Zend\\Mvc\\Controller\\": "setup/src/Zend/Mvc/Controller/" to composer.json "autoload":"psr-4" section
 - Update Magento/Updater if it's installed
 - Update root version label

Usage: php -f $_scriptName -- --root='</path/to/magento/root/>' [--composer='</path/to/composer/executable>'] 
           [--edition='<community|enterprise>'] [--repo='<composer_repo_url>'] [--version='<version_constraint>']
           [--help]

Required:
 --root='</path/to/magento/root/>'
    Path to the Magento installation root directory

Optional:
 --composer='</path/to/composer/executable>'
    Path to the composer executable
    - Default: The composer found in PATH
    
 --edition='<community|enterprise>'
    Target Magento edition for the update.  Open Source = 'community', Commerce = 'enterprise'
    - Default: The edition currently required in composer.json
    
 --repo='<composer_repo_url>'
    The Magento repository url to use to pull the new packages
    - Default: The Magento repository configured in composer.json
    
 --version='<version_constraint>'
    A composer version constraint for allowable 2.3 packages. Versions other than 2.3 are not handled by this script
    See https://getcomposer.org/doc/articles/versions.md#writing-version-constraints for more information.
    - Default: The latest 2.3 version available in the Magento repository

 --help
    Display this message
SYNOPSIS
);

$opts = getopt('', [
    'root:',
    'composer:',
    'edition:',
    'repo:',
    'version:',
    'help'
]);

// Log levels available for use with output() function
define('INFO', 0);
define('WARN', 1);
define('ERROR', 2);

if (isset($opts['help'])) {
    output(SYNOPSIS);
    exit(0);
}

try {
    /**** Populate and Validate Settings ****/

    if (empty($opts['root'])) {
        throw new BadMethodCallException('Magento root must be supplied with --root');
    }

    $rootDir = $opts['root'];
    if (!is_dir($rootDir)) {
        throw new InvalidArgumentException("Supplied Magento root directory '$rootDir' does not exist");
    }

    $tempDir = findUnusedFilename("$rootDir/temp_project");

    // The composer command uses the Magento root as the working directory so this script can be run from anywhere
    $cmd = (!empty($opts['composer']) ? $opts['composer'] : 'composer') . " --working-dir='$rootDir'";

    // Set the version constraint to any 2.3 package if not specified
    $constraint = !empty($opts['version']) ? $opts['version'] : '2.3.*';

    // Grab the root composer.json contents to pull defaults or other data from when necessary
    $composer = json_decode(file_get_contents("$rootDir/composer.json"), true);

    // Get the target Magento edition
    if (!empty($opts['edition'])) {
        $edition = $opts['edition'];
    }
    else {
        $metapackageMatcher = '|^magento/product\-(?<edition>[a-z]+)\-edition$|';

        foreach (array_keys($composer['require']) as $requiredPackage) {
            if (preg_match($metapackageMatcher, $requiredPackage, $matches)) {
                $edition = $matches['edition'];
                break;
            }
        }
        if (empty($edition)) {
            throw new InvalidArgumentException('No Magento metapackage found in composer.json requirements');
        }
    }
    $edition = strtolower($edition);

    if ($edition !== 'community' && $edition !== 'enterprise') {
        throw new InvalidArgumentException("Only 'community' and 'enterprise' editions allowed; '$edition' given");
    }

    // Composer package names
    $project = "magento/project-$edition-edition";
    $metapackage = "magento/product-$edition-edition";

    // Get the list of potential Magento repositories to search for the update package
    $repoUrls = array_map(function ($r) { return $r['url']; }, $composer['repositories']);
    $mageUrls = [];
    if (!empty($opts['repo'])) {
        $mageUrls[] = $opts['repo'];
    }
    else {
        $mageUrls = array_filter($repoUrls, function($u) { return strpos($u, '.mage') !== false; });

        if (count($mageUrls) == 0) {
            throw new InvalidArgumentException('No Magento repository urls found in composer.json');
        }
    }

    $projectConstraint = "$project='$constraint'";
    $version = null;
    $description = null;
    $versionValidator = '/^2\.3\.\d/';

    // Try to retrieve a 2.3 package from each Magento repository until one is found
    foreach ($mageUrls as $repoUrl) {
        try {
            output("Checking $repoUrl for a matching version of $project");
            deleteFilepath($tempDir);
            runComposer("create-project --repository=$repoUrl $projectConstraint $tempDir --no-install");

            // Make sure the downloaded package is 2.3
            $newComposer = json_decode(file_get_contents("$tempDir/composer.json"), true);
            $version = $newComposer['version'];
            $description = $newComposer['description'];

            if (!preg_match($versionValidator, $version)) {
                throw new InvalidArgumentException("Bad 2.3 version constraint '$constraint'; version $version found");
            }

            // If no errors occur, set this as the correct repo, forget errors from previous repos, and move forward
            output("Found $project version $version");
            $repo = $repoUrl;
            unset($exception);
            break;
        }
        catch (Exception $e) {
            // If this repository doesn't have a valid package, save the error but continue checking any others
            output("Failed to find a valid 2.3 $project package on $repoUrl", WARN);
            $exception = $e;
        }
    }

    // If a valid project package hasn't been found, throw the last error
    if (isset($exception)) {
        throw $exception;
    }

    /**** Execute Updates ****/

    $composerBackup = findUnusedFilename("$rootDir/composer.json.bak");
    output("Backing up $rootDir/composer.json to $composerBackup");
    copy("$rootDir/composer.json", $composerBackup);

    // Add the repository to composer.json if needed without overwriting any existing ones
    if (!in_array($repo, $repoUrls)) {
        $repoLabels = array_map('strtolower',array_keys($composer['repositories']));
        $newLabel = 'magento';
        if (in_array($newLabel, $repoLabels)) {
            $i = 1;
            while (in_array("$newLabel-$i", $repoLabels)) $i++;
            $newLabel = "$newLabel-$i";
        }
        output("Adding $repo to composer repositories under label '$newLabel'");
        runComposer("config repositories.$newLabel composer $repo");
    }

    output("Updating Magento metapackage requirement to $metapackage=$version");
    if ($edition == 'enterprise') {
        // Community -> Enterprise upgrades need to remove the community edition metapackage
        runComposer('remove magento/product-community-edition --no-update');
    }
    runComposer("require $metapackage=$version --no-update");

    output('Updating "require-dev" section of composer.json');
    runComposer('require --dev ' .
        'phpunit/phpunit:~6.2.0 ' .
        'friendsofphp/php-cs-fixer:~2.10.1 ' .
        'lusitanian/oauth:~0.8.10 ' .
        'pdepend/pdepend:2.5.2 ' .
        'sebastian/phpcpd:~3.0.0 ' .
        'squizlabs/php_codesniffer:3.2.2 --no-update');
    runComposer('remove --dev sjparkinson/static-review fabpot/php-cs-fixer --no-update');

    output('Adding "Zend\\\\Mvc\\\\Controller\\\\": "setup/src/Zend/Mvc/Controller/" to "autoload": "psr-4"');
    $composer['autoload']['psr-4']['Zend\\Mvc\\Controller\\'] = 'setup/src/Zend/Mvc/Controller/';

    output('Updating root version label from ' . $composer['version'] . " to $version");
    $composer['version'] = $version;

    if ($composer['name'] !== $project) {
        output('Updating root project name and description from ' . $composer['name'] . " to $project");
        $composer['name'] = $project;
        $composer['description'] = $description;
    }

    file_put_contents("$rootDir/composer.json", json_encode($composer, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

    // Update Magento/Updater if it's installed
    if (file_exists("$rootDir/update")) {
        $updateBackup = findUnusedFilename("$rootDir/update.bak");
        output("Backing up Magento/Updater directory $rootDir/update to $updateBackup");
        rename("$rootDir/update", $updateBackup);
        output('Updating Magento/Updater');
        rename("$tempDir/update", "$rootDir/update");
    }

    // Remove temp project directory that was used for repo/version validation and new source for Magento/Updater
    deleteFilepath($tempDir);

    output('\n**** Script Complete! ****');
} catch (Exception $e) {
    if ($e->getPrevious()) {
        $message = (string)$e->getPrevious();
    } else {
        $message = $e->getMessage();
    }

    try {
        output($message . '\n\nScript failed! See usage information with --help', ERROR);

        if (isset($composerBackup) && file_exists($composerBackup)) {
            output('Resetting composer.json backup');
            deleteFilepath("$rootDir/composer.json");
            rename($composerBackup, "$rootDir/composer.json");
        }
        if (isset($updateBackup) && file_exists($updateBackup)) {
            output('Resetting Magento/Updater backup');
            deleteFilepath("$rootDir/update");
            rename($updateBackup, "$rootDir/update");
        }
        if (isset($tempDir) && file_exists($tempDir)) {
            output('Removing temporary project directory');
            deleteFilepath($tempDir);
        }
    }
    catch (Exception $e2) {
        output($e2->getMessage() . '\n\nBackup restoration/directory cleanup failed', ERROR);
    }

    exit($e->getCode() == 0 ? 1 : $e->getCode());
}

/**
 * Gets a variant of a filename that doesn't already exist so we don't overwrite anything
 *
 * @param string $filename
 * @return string
 */
function findUnusedFilename($filename) {
    if (file_exists($filename)) {
        $i = 1;
        while (file_exists($filename . "_$i")) $i++;
        $filename = $filename . "_$i";
    }
    return $filename;
}

/**
 * Execute a composer command and output the results
 *
 * @param string $command
 * @return array Command output split by lines
 * @throws RuntimeException
 */
function runComposer($command)
{
    global $cmd, $composer, $rootDir;
    $command = "$cmd $command";
    output(' Running command: \n  ' . $command);
    exec("$command 2>&1", $lines, $exitCode);
    $output = join(PHP_EOL, $lines);

    // Reload composer object from the updated composer.json
    $composer = json_decode(file_get_contents("$rootDir/composer.json"), true);

    if (0 !== $exitCode) {
        $output = 'Error encountered running command:' . PHP_EOL . " $command" . PHP_EOL . $output;
        throw new RuntimeException($output, $exitCode);
    }
    output($output);
    return $lines;
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

/**
 * Logs the given text with \n newline replacement and log level formatting
 *
 * @param string $string Text to log
 * @param int $level One of INFO, WARN, or ERROR
 */
function output($string, $level = INFO) {
    $string = str_replace('\n', PHP_EOL, $string) . PHP_EOL;

    if ($level == WARN) {
        error_log("WARNING: $string");
    }
    elseif ($level == ERROR) {
        error_log(PHP_EOL . "ERROR: $string");
    }
    else {
        echo $string;
    }
}
