#!/usr/bin/php
<?php
/**
 * Magento repository publishing script
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// get CLI options, define variables
define('SYNOPSIS', <<<SYNOPSIS
php -f publish.php --
    --source="<repository>" --source-point="<branch name or commit ID>"
    --target="<repository>" [--target-branch="<branch>"] [--target-dir="<directory>"]
    --changelog-file="<markdown_file>"
    [--no-push]

SYNOPSIS
);
$options = getopt('', array(
    'source:', 'source-point:', 'target:', 'target-branch::', 'target-dir::', 'changelog-file:', 'no-push'
));
if (empty($options['source']) || empty($options['source-point']) || empty($options['target'])
    || empty($options['changelog-file'])) {
    echo SYNOPSIS;
    exit(1);
}

$sourceRepository = $options['source'];
$targetRepository = $options['target'];
$sourcePoint = $options['source-point'];
$targetBranch = isset($options['target-branch']) ? $options['target-branch'] : 'master';
$targetDir = (isset($options['target-dir']) ? $options['target-dir'] : __DIR__ . '/target');
$changelogFile = $options['changelog-file'];
$canPush = !isset($options['no-push']);

$gitCmd = sprintf('git --git-dir %s --work-tree %s', escapeshellarg("$targetDir/.git"), escapeshellarg($targetDir));

try {
    // clone target repository and attach the source repo as a remote
    execVerbose('git clone %s %s', $targetRepository, $targetDir);
    execVerbose("$gitCmd remote add source %s", $sourceRepository);
    execVerbose("$gitCmd fetch source");
    execVerbose("$gitCmd checkout $targetBranch");

    // determine whether source-point is a branch name or a commit ID
    try {
        $sourceBranch = "source/$sourcePoint";
        execVerbose("$gitCmd rev-parse $sourceBranch");
        $sourcePoint = $sourceBranch;
    } catch (Exception $e) {
        echo "Assuming that 'source-point' is a commit ID." . PHP_EOL;
    }

    $logFile = $targetDir . DIRECTORY_SEPARATOR . $changelogFile;
    $targetLog = file_exists($logFile) ? file_get_contents($logFile) : '';

    // copy new & override existing files in the working tree and index from the source repository
    execVerbose("$gitCmd checkout $sourcePoint -- .");
    // remove files that don't exist in the source repository anymore
    $files = execVerbose("$gitCmd diff --name-only $sourcePoint");
    foreach ($files as $file) {
        execVerbose("$gitCmd rm -f %s", $file);
    }

    // remove files that must not be published
    $listsDir = __DIR__ . '/extruder';
    execVerbose(
        'php -f %s -- -w %s -l %s -l %s -v',
        __DIR__ . '/../extruder.php',
        $targetDir,
        "$listsDir/common.txt",
        "$listsDir/ce.txt",
        "$listsDir/dev_build.txt"
    );

    // compare if changelog is different from the published one, compose the commit message
    if (!file_exists($logFile)) {
        throw new Exception("Changelog file '$logFile' does not exist.");
    }
    $sourceLog = file_get_contents($logFile);
    if (!empty($targetLog) && $sourceLog == $targetLog) {
        throw new Exception("Aborting attempt to publish with old changelog. '$logFile' is not updated.");
    }
    $commitMsg = trim(getTopMarkdownSection($sourceLog));
    if (empty($commitMsg)) {
        throw new Exception("No commit message found in the changelog file '$logFile'.");
    }

    // replace license notices
    $licenseToolDir = __DIR__ . '/license';
    execVerbose(
        'php -f %s -- -w %s -e ce -v -0',
        "$licenseToolDir/license-tool.php",
        $targetDir
    );

    // composer.json
    copy(__DIR__ . '/composer.json_', $targetDir . '/composer.json');
    execVerbose("$gitCmd add composer.json");

    // commit and push
    execVerbose("$gitCmd add --update");
    execVerbose("$gitCmd status");
    execVerbose("$gitCmd commit --message=%s", $commitMsg);
    if ($canPush) {
        execVerbose("$gitCmd push origin $targetBranch");
    }
} catch (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
    exit(1);
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
    exec($command, $output, $exitCode);
    foreach ($output as $line) {
        echo $line . PHP_EOL;
    }
    if (0 !== $exitCode) {
        throw new Exception("Command has failed with exit code: $exitCode.");
    }
    return $output;
}

/**
 * Get the top section of a text in markdown format
 *
 * @param string $contents
 * @return string
 * @link http://daringfireball.net/projects/markdown/syntax
 */
function getTopMarkdownSection($contents)
{
    $parts = preg_split('/^[=\-]+\s*$/m', $contents);
    if (!isset($parts[1])) {
        return '';
    }
    list($title, $body) = $parts;
    $body = explode("\n", trim($body));
    array_pop($body);
    $body = implode("\n", $body);
    return $title . $body;
}
