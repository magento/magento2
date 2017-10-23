<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Util\Generate\File;

use Magento\Mtf\Util\Filesystem\FileHelper;

/**
 * File generator.
 */
class Generator
{
    /**
     * Base directory for files.
     */
    const ROOT_DIRECTORY = '/var/tests/data/';

    /**
     * Directory for saving files.
     *
     * @var string
     */
    private $directory;

    /**
     * Filesystem helper.
     *
     * @var FileHelper
     */
    private $fileHelper;

    /**
     * @param FileHelper $fileHelper
     * @param string $directory
     */
    public function __construct(FileHelper $fileHelper, $directory)
    {
        $this->fileHelper = $fileHelper;
        $this->directory = $this->fileHelper->normalizePath(MTF_BP . static::ROOT_DIRECTORY . $directory);
    }

    /**
     * Method is generate file by template.
     *
     * @param TemplateInterface $template
     * @return string Full path to the generated file.
     * @throws \Exception
     */
    public function generate(TemplateInterface $template)
    {
        $filename = $this->fileHelper->normalizePath($this->directory . '/' . $template->getName());
        if (!$this->fileHelper->createDirectory($this->directory)
            || !$this->fileHelper->createFile($filename, $template->render())
        ) {
            throw new \Exception(
                'Can’t create file with "' . get_class($template) .'" (file "' .  $filename . '").'
            );
        }

        return $filename;
    }
}
