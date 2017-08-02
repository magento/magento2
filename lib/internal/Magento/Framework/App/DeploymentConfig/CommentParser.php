<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Config\CommentParserInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;

/**
 * Parses and retrieves comments from configuration files.
 * @since 2.2.0
 */
class CommentParser implements CommentParserInterface
{
    /**
     * The library to work with file system.
     *
     * @var Filesystem
     * @since 2.2.0
     */
    private $filesystem;

    /**
     * Stores file key to file name config.
     *
     * @var ConfigFilePool
     * @since 2.2.0
     */
    private $configFilePool;

    /**
     * @param Filesystem $filesystem The library to work with file system
     * @param ConfigFilePool $configFilePool Stores file key to file name config
     * @since 2.2.0
     */
    public function __construct(
        Filesystem $filesystem,
        ConfigFilePool $configFilePool
    ) {
        $this->filesystem = $filesystem;
        $this->configFilePool = $configFilePool;
    }

    /**
     * Retrieves list of comments from config file.
     *
     * E.g.,
     * ```php
     * [
     *     'modules' => 'Some comment for the modules section'
     *     'system' => 'Some comment for the system section',
     *     ...
     * ]
     * ```
     *
     * The keys of this array are section names to which the comments relate.
     * The values of this array are comments for these sections.
     *
     * If file with provided name does not exist - empty array will be returned.
     *
     * @param string $fileName The name of config file
     * @return array
     * @since 2.2.0
     */
    public function execute($fileName)
    {
        $result = [];
        $dirReader = $this->filesystem->getDirectoryRead(DirectoryList::CONFIG);

        if (!$dirReader->isExist($fileName)) {
            return $result;
        }

        $fileContent = $dirReader->readFile($fileName);
        $commentBlocks = array_filter(
            token_get_all($fileContent),
            function ($entry) {
                return T_DOC_COMMENT == $entry[0];
            }
        );

        foreach ($commentBlocks as $commentBlock) {
            $text = $this->getCommentText($commentBlock[1]);
            $section = $this->getSectionName($commentBlock[1]);

            if ($section && $text) {
                $result[$section] = $text;
            }
        }

        return $result;
    }

    /**
     * Retrieves text of comment.
     *
     * @param string $commentBlock The comment
     * @return string|null
     * @since 2.2.0
     */
    private function getCommentText($commentBlock)
    {
        $commentsLine = [];
        foreach (preg_split("/(\r?\n)/", $commentBlock) as $commentLine) {
            if (preg_match('/^(?=\s+?\*[^\/])(.+)/', $commentLine, $matches)
                && false === strpos($commentLine, 'For the section')
            ) {
                $commentsLine[] = preg_replace('/^(\*\s?)/', '', trim($matches[1]));
            }
        }

        return empty($commentsLine) ? null : implode(PHP_EOL, $commentsLine);
    }

    /**
     * Retrieves section name to which the comment relates.
     *
     * @param string $comment The comment
     * @return string|null
     * @since 2.2.0
     */
    private function getSectionName($comment)
    {
        $pattern = '/\s+\* For the section: (.+)\s/';
        preg_match_all($pattern, $comment, $matches);

        return empty($matches[1]) ? null : trim(array_shift($matches[1]));
    }
}
