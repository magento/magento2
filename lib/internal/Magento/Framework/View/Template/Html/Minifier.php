<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Template\Html;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Class \Magento\Framework\View\Template\Html\Minifier
 *
 */
class Minifier implements MinifierInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * All inline HTML tags
     *
     * @var array
     */
    protected $inlineHtmlTags = [
        'b',
        'big',
        'i',
        'small',
        'tt',
        'abbr',
        'acronym',
        'cite',
        'code',
        'dfn',
        'em',
        'kbd',
        'strong',
        'samp',
        'var',
        'a',
        'bdo',
        'br',
        'img',
        'map',
        'object',
        'q',
        'span',
        'sub',
        'sup',
        'button',
        'input',
        'label',
        'select',
        'textarea',
        '\?',
    ];

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $htmlDirectory;

    /**
     * @var Filesystem\Directory\ReadFactory
     * @since 2.1.0
     */
    protected $readFactory;

    /**
     * @param Filesystem $filesystem
     * @param Filesystem\Directory\ReadFactory $readFactory
     */
    public function __construct(
        Filesystem $filesystem,
        Filesystem\Directory\ReadFactory $readFactory
    ) {
        $this->filesystem = $filesystem;
        $this->htmlDirectory = $filesystem->getDirectoryWrite(DirectoryList::TMP_MATERIALIZATION_DIR);
        $this->readFactory = $readFactory;
    }

    /**
     * Return path to minified template file, or minify if file not exist
     *
     * @param string $file
     * @return string
     */
    public function getMinified($file)
    {
        $file = $this->htmlDirectory->getDriver()->getRealPathSafety($file);
        if (!$this->htmlDirectory->isExist($this->getRelativeGeneratedPath($file))) {
            $this->minify($file);
        }
        return $this->getPathToMinified($file);
    }

    /**
     * Return path to minified template file
     *
     * @param string $file
     * @return string
     */
    public function getPathToMinified($file)
    {
        return $this->htmlDirectory->getAbsolutePath($this->getRelativeGeneratedPath($file));
    }

    /**
     * Minify template file
     *
     * @param string $file
     * @return void
     */
    public function minify($file)
    {
        $dir = dirname($file);
        $fileName = basename($file);
        $content = preg_replace(
            '#(?<!]]>)\s+</#',
            '</',
            preg_replace(
                '#((?:<\?php\s+(?!echo|print|if|elseif|else)[^\?]*)\?>)\s+#',
                '$1 ',
                preg_replace(
                    '#(?<!' . implode('|', $this->inlineHtmlTags) . ')\> \<#',
                    '><',
                    preg_replace(
                        '#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:textarea|pre|script)\b))*+)'
                        . '(?:<(?>textarea|pre|script)\b|\z))#',
                        ' ',
                        preg_replace(
                            '#(?<!:|\\\\|\'|")//(?!\s*\<\!\[)(?!\s*]]\>)[^\n\r]*#',
                            '',
                            preg_replace(
                                '#(?<!:|\'|")//[^\n\r]*(\s\?\>)#',
                                '$1',
                                preg_replace(
                                    '#(?<!:)//[^\n\r]*(\<\?php)[^\n\r]*(\s\?\>)[^\n\r]*#',
                                    '',
                                    $this->readFactory->create($dir)->readFile($fileName)
                                )
                            )
                        )
                    )
                )
            )
        );

        if (!$this->htmlDirectory->isExist()) {
            $this->htmlDirectory->create();
        }
        $this->htmlDirectory->writeFile($this->getRelativeGeneratedPath($file), rtrim($content));
    }

    /**
     * Gets the relative path of minified file to generation directory
     *
     * @param string $sourcePath
     * @return string
     * @since 2.1.0
     */
    private function getRelativeGeneratedPath($sourcePath)
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getRelativePath($sourcePath);
    }
}
