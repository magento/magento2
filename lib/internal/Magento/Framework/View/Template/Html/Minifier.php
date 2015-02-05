<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    ];

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->appDirectory = $filesystem->getDirectoryRead(DirectoryList::APP);
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
        if (!$this->htmlDirectory->isExist($this->appDirectory->getRelativePath($file))) {
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
            $this->appDirectory->getRelativePath($file)
        );
    }

    /**
     * Minify template file
     *
     * @param string $file
     */
    public function minify($file)
    {
        $file = $this->appDirectory->getRelativePath($file);
        $content = preg_replace(
            '#(?<!' . implode('|', $this->inlineHtmlTags) . ')(?<!\?)\> \<#',
            '><',
            preg_replace(
                '#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:textarea|pre|script)\b))*+)'
                . '(?:<(?>textarea|pre|script)\b|\z))#',
                ' ',
                preg_replace(
                    '#(?<!:)//(?!\<\!\[)(?!]]\>)[^\n\r]*#',
                    '',
                    preg_replace(
                        '#(?<!:)//[^\n\r]*(\s\?\>)#',
                        '$1',
                        $this->appDirectory->readFile($file)
                    )
                )
            )
        );

        if (!$this->htmlDirectory->isExist()) {
            $this->htmlDirectory->create();
        }
        $this->htmlDirectory->writeFile($file, $content);
    }
}
