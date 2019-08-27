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
Run this script after upgrading to PHP 7.1/7.2 and before running `composer update` or `bin/magento setup:upgrade`.

Steps included:
 - Require new version of the metapackage
 - Update "require-dev" section
 - Add "Zend\\Mvc\\Controller\\": "setup/src/Zend/Mvc/Controller/" to composer.json "autoload":"psr-4" section
 - Update Magento/Updater if it's installed
 - Update name, version, and description fields in the root composer.json 

Usage: php -f $_scriptName -- --root='</path/to/magento/root/>' [--composer='</path/to/composer/executable>'] 
           [--edition='<community|enterprise>'] [--repo='<composer_repo_url>'] [--version='<version_constraint>']
           [--help]

Required:
 --root='</path/to/magento/root/>'
    Path to the Magento installation root directory

Optional:
 --composer='</path/to/composer/executable>'
    Path to the composer executable
    - Default: The composer found in the system PATH
    
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
    if (version_compare(PHP_VERSION, '7.1', '<') || version_compare(PHP_VERSION, '7.3', '>=')) {
        preg_match('/^\d+\.\d+\.\d+/',PHP_VERSION, $matches);
        $phpVersion = $matches[0];
        throw new Exception("Invalid PHP version '$phpVersion'. Magento 2.3 requires PHP 7.1 or 7.2");
    }

    /**** Populate and Validate Settings ****/

    if (empty($opts['root']) || !is_dir($opts['root'])) {
        throw new BadMethodCallException('Existing Magento root directory must be supplied with --root');
    }
    $rootDir = $opts['root'];

    $composerFile = "$rootDir/composer.json";
    if (!file_exists($composerFile)) {
        throw new InvalidArgumentException("Supplied Magento root directory '$rootDir' does not contain composer.json");
    }

    $composerData = json_decode(file_get_contents($composerFile), true);

    $metapackageMatcher = '/^magento\/product\-(?<edition>community|enterprise)\-edition$/';
    foreach (array_keys($composerData['require']) as $requiredPackage) {
        if (preg_match($metapackageMatcher, $requiredPackage, $matches)) {
            $edition = $matches['edition'];
            break;
        }
    }
    if (empty($edition)) {
        throw new InvalidArgumentException("No Magento metapackage found in $composerFile");
    }

    // Override composer.json edition if one is passed to the script
    if (!empty($opts['edition'])) {
        $edition = $opts['edition'];
    }
    $edition = strtolower($edition);

    if ($edition !== 'community' && $edition !== 'enterprise') {
        throw new InvalidArgumentException("Only 'community' and 'enterprise' editions allowed; '$edition' given");
    }

    $composerExec = (!empty($opts['composer']) ? $opts['composer'] : 'composer');
    if (basename($composerExec, '.phar') != 'composer') {
        throw new InvalidArgumentException("'$composerExec' is not a composer executable");
    }

    // Use 'command -v' to check if composer is executable
    exec("command -v $composerExec", $out, $composerFailed);
    if ($composerFailed) {
        if ($composerExec == 'composer') {
            $message = 'Composer executable is not available in the system PATH';
        }
        else {
            $message = "Invalid composer executable '$composerExec'";
        }
        throw new InvalidArgumentException($message);
    }

    // The composer command uses the Magento root as the working directory so this script can be run from anywhere
    $composerExec = "$composerExec --working-dir='$rootDir'";

    // Set the version constraint to any 2.3 package if not specified
    $constraint = !empty($opts['version']) ? $opts['version'] : '2.3.*';

    // Composer package names
    $project = "magento/project-$edition-edition";
    $metapackage = "magento/product-$edition-edition";

    // Get the list of potential Magento repositories to search for the update package
    $mageUrls = [];
    $authFailed = [];
    if (!empty($opts['repo'])) {
        $mageUrls[] = $opts['repo'];
    }
    else {
        foreach ($composerData['repositories'] as $label => $repo) {
            if (is_string($label) && strpos(strtolower($label), 'mage') !== false || strpos($repo['url'], '.mage') !== false) {
                $mageUrls[] = $repo['url'];
            }
        }

        if (count($mageUrls) == 0) {
            throw new InvalidArgumentException('No Magento repository urls found in composer.json');
        }
    }

    $tempDir = findUnusedFilename($rootDir, 'temp_project');
    $projectConstraint = "$project='$constraint'";
    $version = null;
    $description = null;

    output("**** Searching for a matching version of $project ****");

    // Try to retrieve a 2.3 package from each Magento repository until one is found
    foreach ($mageUrls as $repoUrl) {
        try {
            output("\\nChecking $repoUrl");
            deleteFilepath($tempDir);
            runComposer("create-project --repository=$repoUrl $projectConstraint $tempDir --no-install");

            // Make sure the downloaded package is 2.3
            $newComposer = json_decode(file_get_contents("$tempDir/composer.json"), true);
            $version = $newComposer['version'];
            $description = $newComposer['description'];

            if (strpos($version, '2.3.') !== 0) {
                throw new InvalidArgumentException("Bad 2.3 version constraint '$constraint'; version $version found");
            }

            // If no errors occurred, set this as the correct repo, forget errors from previous repos, and move forward
            output("\\n**** Found compatible $project version: $version ****");
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

    output("\\n**** Executing Updates ****");

    $composerBackup = findUnusedFilename($rootDir, 'composer.json.bak');
    output("\\nBacking up $composerFile to $composerBackup");
    copy($composerFile, $composerBackup);

    // Add the repository to composer.json if needed without overwriting any existing ones
    $repoUrls = array_map(function ($r) { return $r['url']; }, $composerData['repositories']);
    if (!in_array($repo, $repoUrls)) {
        $repoLabels = array_map('strtolower',array_keys($composerData['repositories']));
        $newLabel = 'magento';
        if (in_array($newLabel, $repoLabels)) {
            $count = count($repoLabels);
            for ($i = 1; $i <= $count; $i++) {
                if (!in_array("$newLabel-$i", $repoLabels)) {
                    $newLabel = "$newLabel-$i";
                    break;
                }
            }
        }
        output("\\nAdding $repo to composer repositories under label '$newLabel'");
        runComposer("config repositories.$newLabel composer $repo");
    }

    output("\\nUpdating Magento metapackage requirement to $metapackage=$version");
    if ($edition == 'enterprise') {
        // Community -> Enterprise upgrades need to remove the community edition metapackage
        runComposer('remove magento/product-community-edition --no-update');
        output('');
    }
    runComposer("require $metapackage=$version --no-update");

    output('\nUpdating "require-dev" section of composer.json');
    runComposer('require --dev ' .
        'allure-framework/allure-phpunit:~1.2.0 ' .
        'friendsofphp/php-cs-fixer:~2.14.0 ' .
        'lusitanian/oauth:~0.8.10 ' .
        'magento/magento-coding-standard:~3.0.0 ' .
        'magento/magento2-functional-testing-framework:~2.4.3 ' .
        'pdepend/pdepend:2.5.2 ' .
        'phpmd/phpmd:@stable ' .
        'phpunit/phpunit:~6.5.0 ' .
        'sebastian/phpcpd:~3.0.0 ' .
        'squizlabs/php_codesniffer:3.4.0 ' .
        '--sort-packages --no-update');
    output('');
    runComposer('remove --dev sjparkinson/static-review fabpot/php-cs-fixer --no-update');

    output('\nAdding "Zend\\\\Mvc\\\\Controller\\\\": "setup/src/Zend/Mvc/Controller/" to "autoload": "psr-4"');
    $composerData['autoload']['psr-4']['Zend\\Mvc\\Controller\\'] = 'setup/src/Zend/Mvc/Controller/';

    if (preg_match('/^magento\/project\-(community|enterprise)\-edition$/', $composerData['name'])) {
        output('\nUpdating project name, version, and description');
        $composerData['name'] = $project;
        $composerData['version'] = $version;
        $composerData['description'] = $description;
    }

    file_put_contents($composerFile, json_encode($composerData, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

    // Update Magento/Updater if it's installed
    $updateDir = "$rootDir/update";
    if (file_exists($updateDir)) {
        $updateBackup = findUnusedFilename($rootDir, 'update.bak');
        output("\\nBacking up Magento/Updater directory $updateDir to $updateBackup");
        rename($updateDir, $updateBackup);
        output('\nUpdating Magento/Updater');
        rename("$tempDir/update", $updateDir);
    }

    // Remove temp project directory that was used for repo/version validation and new source for Magento/Updater
    deleteFilepath($tempDir);

    output("\\n**** Script Complete! $composerFile updated to Magento version $version ****");
    if (count($authFailed) > 0) {
        output('Repository authentication failures occurred!', WARN);
        output(' * Failed authentication could result in incorrect package versions', WARN);
        output(' * To resolve, add credentials for the repositories to auth.json', WARN);
        output(' * URL(s) failing authentication: ' . join(', ', array_keys($authFailed)), WARN);
    }
} catch (Exception $e) {
    if ($e->getPrevious()) {
        $e = $e->getPrevious();
    }

    try {
        output($e->getMessage(), ERROR, get_class($e));
        output('Script failed! See usage information with --help', ERROR);

        if (isset($composerBackup) && file_exists($composerBackup)) {
            output("Resetting $composerFile backup");
            deleteFilepath($composerFile);
            rename($composerBackup, $composerFile);
        }
        if (isset($updateBackup) && file_exists($updateBackup)) {
            output("Resetting $updateDir backup");
            deleteFilepath($updateDir);
            rename($updateBackup, $updateDir);
        }
        if (isset($tempDir) && file_exists($tempDir)) {
            output('Removing temporary project directory');
            deleteFilepath($tempDir);
        }
    }
    catch (Exception $e2) {
        output($e2->getMessage(), ERROR, get_class($e2));
        output('Backup restoration or directory cleanup failed', ERROR);
    }

    exit($e->getCode() == 0 ? 1 : $e->getCode());
}

/**
 * Gets a variant of a filename that doesn't already exist so we don't overwrite anything
 *
 * @param string $dir
 * @param string $filename
 * @return string
 */
function findUnusedFilename($dir, $filename) {
    $unique = "$dir/$filename";
    if (file_exists($unique)) {
        $unique = tempnam($dir, "$filename.");
        unlink($unique);
    }
    return $unique;
}

/**
 * Execute a composer command, reload $composerData afterwards, and check for repo authentication warnings
 *
 * @param string $command
 * @return array Command output split by lines
 * @throws RuntimeException
 */
function runComposer($command)
{
    global $composerExec, $composerData, $composerFile, $authFailed;
    $command = "$composerExec $command --no-interaction";
    output(" Running command:\\n  $command");
    exec("$command 2>&1", $lines, $exitCode);
    $output = '    ' . join('\n    ', $lines);

    // Reload composer object from the updated composer.json
    $composerData = json_decode(file_get_contents($composerFile), true);

    if (0 !== $exitCode) {
        $output = "Error encountered running command:\\n $command\\n$output";
        throw new RuntimeException($output, $exitCode);
    }
    output($output);

    if (strpos($output, 'URL required authentication.') !== false) {
        preg_match("/'(https?:\/\/)?(?<url>[^\/']+)(\/[^']*)?' URL required authentication/", $output, $matches);
        $authUrl = $matches['url'];
        $authFailed[$authUrl] = 1;
        output("Repository authentication failed; make sure '$authUrl' exists in auth.json", WARN);
    }

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
 * @param string $label Optional message label; defaults to WARNING for $level = WARN and ERROR for $level = ERROR
 */
function output($string, $level = INFO, $label = '') {
    $string = str_replace('\n', PHP_EOL, $string);

    if (!empty($label)) {
        $label = "$label: ";
    }
    else if ($level == WARN) {
        $label = 'WARNING: ';
    }
    else if ($level == ERROR) {
        $label = 'ERROR: ';
    }
    $string = "$label$string";

    if ($level == WARN) {
        error_log($string);
    }
    elseif ($level == ERROR) {
        error_log(PHP_EOL . $string);
    }
    else {
        echo $string . PHP_EOL;
    }
}
