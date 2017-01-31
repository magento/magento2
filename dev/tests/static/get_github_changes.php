<?php

/**
 * Script to get changes between feature branch and the mainline
 *
 * @category   dev
 * @package    build
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

define(
    'USAGE',
    <<<USAGE
        php -f get_github_changes.php --
    --output-file="<output_file>"
    --base-path="<base_path>"
    --repo="<main_repo>"
    --branch="<branch>"
    [--file-extensions="<comma_separated_list_of_formats>"]

USAGE
);

$options = getopt('', ['output-file:', 'base-path:', 'repo:', 'file-extensions:', 'branch:']);

$requiredOptions = ['output-file', 'base-path', 'repo', 'branch'];
if (!validateInput($options, $requiredOptions)) {
    echo USAGE;
    exit(1);
}

$fileExtensions = explode(',', isset($options['file-extensions']) ? $options['file-extensions'] : 'php');

$mainline = 'mainline_' . (string)rand(0, 9999);
$repo = getRepo($options, $mainline);
$branches = $repo->getBranches('--remotes');
generateBranchesList($options['output-file'], $branches, $options['branch']);
$changes = retrieveChangesAcrossForks($mainline, $repo, $options['branch']);
$changedFiles = getChangedFiles($changes, $fileExtensions);
generateChangedFilesList($options['output-file'], $changedFiles);
cleanup($repo, $mainline);

/**
 * Generates a file containing changed files
 *
 * @param string $outputFile
 * @param array $changedFiles
 * @return void
 */
function generateChangedFilesList($outputFile, $changedFiles)
{
    $changedFilesList = fopen($outputFile, 'w');
    foreach ($changedFiles as $file) {
        fwrite($changedFilesList, $file . PHP_EOL);
    }
    fclose($changedFilesList);
}

/**
 * Generates a file containing origin branches
 *
 * @param string $outputFile
 * @param array $branches
 * @param string $branchName
 * @return void
 */
function generateBranchesList($outputFile, $branches, $branchName)
{
    $branchOutputFile = str_replace('changed_files', 'branches', $outputFile);
    $branchesList = fopen($branchOutputFile, 'w');
    fwrite($branchesList, $branchName . PHP_EOL);
    foreach ($branches as $branch) {
        fwrite($branchesList, substr(strrchr($branch, '/'), 1) . PHP_EOL);
    }
    fclose($branchesList);
}

/**
 * Gets list of changed files
 *
 * @param array $changes
 * @param array $fileExtensions
 * @return array
 */
function getChangedFiles(array $changes, array $fileExtensions)
{
    $files = [];
    foreach ($changes as $fileName) {
        foreach ($fileExtensions as $extensions) {
            $isFileExension = strpos($fileName, '.' . $extensions);
            if ($isFileExension) {
                $files[] = $fileName;
            }
        }
    }

    return $files;
}

/**
 * Retrieves changes across forks
 *
 * @param array $options
 * @param string $mainline
 * @return array
 * @throws Exception
 */
function getRepo($options, $mainline)
{
    $repo = new GitRepo($options['base-path']);
    $repo->addRemote($mainline, $options['repo']);
    $repo->fetch($mainline);
    return $repo;
}

/**
 * @param string $mainline
 * @param GitRepo $repo
 * @param string $branchName
 * @return array
 */
function retrieveChangesAcrossForks($mainline, GitRepo $repo, $branchName)
{
    return $repo->compareChanges($mainline, $branchName);
}

/**
 * Deletes temporary "base" repo
 *
 * @param GitRepo $repo
 * @param string $repo
 */
function cleanup($repo, $mainline)
{
    $repo->removeRemote($mainline);
}

/**
 * Validates input options based on required options
 *
 * @param array $options
 * @param array $requiredOptions
 * @return bool
 */
function validateInput(array $options, array $requiredOptions)
{
    foreach ($requiredOptions as $requiredOption) {
        if (!isset($options[$requiredOption]) || empty($options[$requiredOption])) {
            return false;
        }
    }
    return true;
}


class GitRepo
{
    /**
     * Absolute path to git project
     *
     * @var string
     */
    private $workTree;

    /**
     * @var array
     */
    private $remoteList = [];

    /**
     * @param string $workTree absolute path to git project
     */
    public function __construct($workTree)
    {
        if (empty($workTree) || !is_dir($workTree)) {
            throw new UnexpectedValueException('Working tree should be a valid path to directory');
        }
        $this->workTree = $workTree;
    }

    /**
     * Adds remote
     *
     * @param string $alias
     * @param string $url
     */
    public function addRemote($alias, $url)
    {
        if (isset($this->remoteList[$alias])) {
            return;
        }
        $this->remoteList[$alias] = $url;

        $this->call(sprintf('remote add %s %s', $alias, $url));
    }

    /**
     * Remove remote
     *
     * @param string $alias
     */
    public function removeRemote($alias)
    {
        if (isset($this->remoteList[$alias])) {
            $this->call(sprintf('remote rm %s', $alias));
            unset($this->remoteList[$alias]);
        }
    }

    /**
     * Fetches remote
     *
     * @param string $remoteAlias
     */
    public function fetch($remoteAlias)
    {
        if (!isset($this->remoteList[$remoteAlias])) {
            throw new LogicException('Alias "' . $remoteAlias . '" is not defined');
        }

        $this->call(sprintf('fetch %s', $remoteAlias));
    }

    /**
     * Returns repo branches
     *
     * @param string $source
     * @return array|mixed
     */
    public function getBranches($source = '--all')
    {
        $result = $this->call(sprintf('branch ' . $source));

        return is_array($result) ? $result : [];
    }

    /**
     * Returns files changes between branch and HEAD
     *
     * @param string $remoteAlias
     * @param string $remoteBranch
     * @return array
     */
    public function compareChanges($remoteAlias, $remoteBranch)
    {
        if (!isset($this->remoteList[$remoteAlias])) {
            throw new LogicException('Alias "' . $remoteAlias . '" is not defined');
        }

        $result = $this->call(sprintf('log %s/%s..HEAD  --name-status --oneline', $remoteAlias, $remoteBranch));

        return is_array($result)
            ? $this->filterChangedFiles($result,
                $remoteAlias,
                $remoteBranch
            )
            : [];
    }

    /**
     * Makes a diff of file for specified remote/branch and filters only those have real changes
     *
     * @param array $changes
     * @param string $remoteAlias
     * @param string $remoteBranch
     * @return array
     */
    protected function filterChangedFiles(array $changes, $remoteAlias, $remoteBranch)
    {
        $changedFilesMasks = [
            'M' => "M\t",
            'A' => "A\t"
        ];
        $filteredChanges = [];
        foreach ($changes as $fileName) {
            foreach ($changedFilesMasks as $mask) {
                if (strpos($fileName, $mask) === 0) {
                    $fileName = str_replace($mask, '', $fileName);
                    $fileName = trim($fileName);
                    if (!in_array($fileName, $filteredChanges) && is_file($this->workTree . '/' . $fileName)) {
                        $result = $this->call(sprintf(
                                'diff HEAD %s/%s -- %s', $remoteAlias, $remoteBranch, $this->workTree . '/' . $fileName)
                        );
                        if ($result) {
                            $filteredChanges[] = $fileName;
                        }
                    }
                    break;
                }
            }
        }
        return $filteredChanges;
    }

    /**
     * Makes call ro git cli
     *
     * @param string $command
     * @return mixed
     */
    private function call($command)
    {
        $gitCmd = sprintf(
            'git --git-dir %s --work-tree %s',
            escapeshellarg("{$this->workTree}/.git"),
            escapeshellarg($this->workTree)
        );
        $tmp = sprintf('%s %s', $gitCmd, $command);
        exec($tmp, $output);
        return $output;
    }
}
