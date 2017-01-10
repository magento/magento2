<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 * Class Comment. It is used to parse config paths from comment section.
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
     */
    public function __construct(
        Filesystem $filesystem,
        PlaceholderInterface $placeholder
    ) {
        $this->filesystem = $filesystem;
        $this->placeholder = $placeholder;
    }

    /**
     * Retrieves config paths from comment section of the file.
     * Example of comment:
     *        * CONFIG__DEFAULT__SOME__CONF__PATH_ONE
     *        * CONFIG__DEFAULT__SOME__CONF__PATH_TWO
     * This method will return:
     *        array(
     *            'CONFIG__DEFAULT__SOME__CONF__PATH_ONE' => 'some/conf/path_one',
     *            'CONFIG__DEFAULT__SOME__CONF__PATH_TWO' => 'some/conf/path_two'
     *        );
     *
     * @param string $fileName
     * @return array
     * @throws FileSystemException
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
