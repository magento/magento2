<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * @var Filesystem\Directory\WriteInterface
     */
    protected $htmlDirectory;

    /**
     * @var Filesystem\Directory\ReadFactory
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
        $content = $this->readFactory->create($dir)->readFile($fileName);
        //Storing Heredocs
        $heredocs = [];
        $content = preg_replace_callback(
            '/<<<([A-z]+).*?\1;/ims',
            function ($match) use (&$heredocs) {
                $heredocs[] = $match[0];

                return '__MINIFIED_HEREDOC__' .(count($heredocs) - 1);
            },
            // phpstan:ignore
            ($content ?? '')
        );
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
                            '#(?<!:|\\\\|\'|"|/)//(?!/)(?!\s*\<\!\[)(?!\s*]]\>)[^\n\r]*#',
                            '',
                            preg_replace(
                                '#(?<!:|\'|")//[^\n\r<]*(\?\>)#',
                                ' $1',
                                preg_replace(
                                    '#(?<!:)//[^\n\r(\?\>)]*(\<\?php)[^\n\r]*(\s\?\>)[^\n\r]*#',
                                    '',
                                    ($content ?? '')
                                )
                            )
                        )
                    )
                )
            )
        );

        //Restoring Heredocs
        $content = preg_replace_callback(
            '/__MINIFIED_HEREDOC__(\d+)/ims',
            function ($match) use ($heredocs) {
                return $heredocs[(int)$match[1]];
            },
            ($content ?? '')
        );

        if (!$this->htmlDirectory->isExist()) {
            $this->htmlDirectory->create();
        }
        $this->htmlDirectory->writeFile(
            $this->getRelativeGeneratedPath($file),
            $content !== null ? rtrim($content) : ''
        );
    }

    /**
     * Gets the relative path of minified file to generation directory
     *
     * @param string $sourcePath
     * @return string
     */
    private function getRelativeGeneratedPath($sourcePath)
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getRelativePath($sourcePath);
    }
}
