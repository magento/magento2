<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Template\Html;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Asset\Source;

class Minifier
{
    const HTML = 'html';

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * @param File $fileDriver
     */
    public function __construct(
        File $fileDriver
    ) {
        $this->fileDriver = $fileDriver;
    }

    public static function getDirectory()
    {
        return BP . DIRECTORY_SEPARATOR
            . DirectoryList::VAR_DIR . DIRECTORY_SEPARATOR
            . Source::TMP_MATERIALIZATION_DIR
            . DIRECTORY_SEPARATOR . self::HTML;
    }

    public function getMinifyFile($file)
    {
        if (!$this->fileDriver->isExists($this->getNewFilePath($file))) {
            $this->minify($file);
        }
        return $this->getNewFilePath($file);
    }

    public function getNewFilePath($file)
    {
        return $this->getDirectory() . $this->getRelativePath($file);
    }

    public function getRelativePath($file)
    {
        return preg_replace('#^' . BP . '#', '', $file);
    }

    public function minify($file)
    {
        $content = preg_replace(
            '#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:textarea|pre)\b))*+)(?:<(?>textarea|pre)\b|\z))#',
            ' ',
            preg_replace(
                '#\<\?php\s*\?\>#',
                '',
                preg_replace(
                    '#/\*.*?\*/#s',
                    '',
                    preg_replace(
                        '![ \t]*//.*[ \t]*[\r\n]!',
                        '',
                        $this->fileDriver->fileGetContents($file)
                    )
                )
            )
        );

        if (!$this->fileDriver->isExists(dirname($this->getNewFilePath($file)))) {
            $this->fileDriver->createDirectory(dirname($this->getNewFilePath($file)), 0777);
        }
        $this->fileDriver->filePutContents($this->getNewFilePath($file), $content);
    }
}