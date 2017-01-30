<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Template\Html;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

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
     * @var Filesystem\Directory\ReadInterface
     */
    protected $rootDirectory;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $htmlDirectory;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->htmlDirectory = $filesystem->getDirectoryWrite(DirectoryList::TEMPLATE_MINIFICATION_DIR);
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
        if (!$this->htmlDirectory->isExist($this->rootDirectory->getRelativePath($file))) {
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
        return $this->htmlDirectory->getAbsolutePath(
            $this->rootDirectory->getRelativePath($file)
        );
    }

    /**
     * Minify template file
     *
     * @param string $file
     * @return void
     */
    public function minify($file)
    {
        $file = $this->rootDirectory->getRelativePath($file);
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
                                '#(?<!:)//[^\n\r]*(\s\?\>)#',
                                '$1',
                                preg_replace(
                                    '#//[^\n\r]*(\<\?php)[^\n\r]*(\s\?\>)[^\n\r]*#',
                                    '',
                                    $this->rootDirectory->readFile($file)
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
        $this->htmlDirectory->writeFile($file, rtrim($content));
    }
}
