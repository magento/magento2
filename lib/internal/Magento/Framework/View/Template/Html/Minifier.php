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
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::APP);
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
     */
    public function minify($file)
    {
        $file = $this->rootDirectory->getRelativePath($file);
        $content = preg_replace(
            '#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:textarea|pre|script)\b))*+)(?:<(?>textarea|pre|script)\b|\z))#',
            ' ',
            preg_replace(
                '#(?<!:)//(?!\<\!\[)(?!]]\>)[^\n\r]*#',
                '',
                preg_replace(
                    '#//[^\n\r]*(\s\?\>)#',
                    '$1',
                    $this->rootDirectory->readFile($file)
                )
            )
        );

        if (!$this->htmlDirectory->isExist()) {
            $this->htmlDirectory->create();
        }
        $this->htmlDirectory->writeFile($file, $content);
    }
}