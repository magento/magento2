<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Parser;

use Magento\Config\Model\Placeholder\Environment;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\App\Config\CommentParserInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;

/**
 * Config file parser
 *
 * It is used to parse config paths from
 * comment section in provided configuration file.
 * @api
 * @since 100.2.0
 */
class Comment implements CommentParserInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var PlaceholderInterface
     */
    private $placeholder;

    /**
     * @param Filesystem $filesystem
     * @param PlaceholderInterface $placeholder
     * @since 100.2.0
     */
    public function __construct(
        Filesystem $filesystem,
        PlaceholderInterface $placeholder
    ) {
        $this->filesystem = $filesystem;
        $this->placeholder = $placeholder;
    }

    /**
     * Retrieves config array paths from comment section of the config file
     *
     * Example:
     * some.file.config.php file is located in the directory /<root_app_dir>/app/etc/
     * File Content some.file.config.php
     * ```php
     * return [
     *  'scopes' => [
     *      //...
     *  ],
     * // ...
     * // Sensitive data can be stored in the following environment variables:
     * // CONFIG__DEFAULT__SOME__CONF__PATH_ONE for some/conf/path_one
     *  'system' => [],
     * // ...
     * // CONFIG__DEFAULT__SOME__CONF__PATH_TWO for some/conf/path_two
     * // ...
     * ];
     *  ```
     * Usage:
     * ```php
     * // ...
     * // $commentParser variable contains an object of type \Magento\Config\Model\Config\Parser\Comment
     * $fileName = 'some.file.config.php';
     * $result = $commentParser->execute($fileName);
     * // ...
     * ```
     * The variable $result will be set to
     * ```php
     * array(
     *     'CONFIG__DEFAULT__SOME__CONF__PATH_ONE' => 'some/conf/path_one',
     *     'CONFIG__DEFAULT__SOME__CONF__PATH_TWO' => 'some/conf/path_two'
     * );
     * ```
     *
     * @param string $fileName the basename of file
     * @return array
     * @throws FileSystemException
     * @since 100.2.0
     */
    public function execute($fileName)
    {
        $fileContent = $this->filesystem
            ->getDirectoryRead(DirectoryList::CONFIG)
            ->readFile($fileName);

        $pattern = sprintf('/\s+\*\s+(?P<placeholder>%s.*?)\s/', preg_quote(Environment::PREFIX));
        preg_match_all($pattern, $fileContent, $matches);

        if (!isset($matches['placeholder'])) {
            return [];
        }

        $configs = [];
        foreach ($matches['placeholder'] as $placeholder) {
            $path = $this->placeholder->restore($placeholder);
            $path = preg_replace('/^' . ScopeConfigInterface::SCOPE_TYPE_DEFAULT . '\//', '', $path);
            $configs[$placeholder] = $path;
        }

        return $configs;
    }
}
