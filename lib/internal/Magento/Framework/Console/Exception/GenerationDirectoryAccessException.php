<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console\Exception;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Phrase;

class GenerationDirectoryAccessException extends FileSystemException
{
    /**
     * @inheritdoc
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        $phrase = $phrase ?: new Phrase(
            'Command line user does not have read and write permissions on '
            . $this->getDefaultDirectoryPath(DirectoryList::GENERATED_CODE) . ' directory. '
            . 'Please address this issue before using Magento command line.'
        );

        parent::__construct($phrase, $cause, $code);
    }

    /**
     * Get default directory path by code
     *
     * @param string $code
     * @return string
     */
    private function getDefaultDirectoryPath($code)
    {
        $config = DirectoryList::getDefaultConfig();
        $result = '';

        if (isset($config[$code][DirectoryList::PATH])) {
            $result = $config[$code][DirectoryList::PATH];
        }

        return $result;
    }
}
