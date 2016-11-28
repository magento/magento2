<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

use Magento\Framework\App\Utility\Files;
use Magento\TestFramework\Utility\File\RegexIteratorFactory;

/**
 * Get list of PHP files including files in setup application
 */
class File
{
    /**
     * @var RegexIteratorFactory
     */
    private $regexIteratorFactory;

    /**
     * @var Files
     */
    private $fileUtilities;

    /**
     * Constructor
     *
     * @param Files $fileUtilities
     * @param RegexIteratorFactory $regexIteratorFactory
     */
    public function __construct(
        Files $fileUtilities,
        RegexIteratorFactory $regexIteratorFactory
    ) {
        $this->fileUtilities = $fileUtilities;
        $this->regexIteratorFactory = $regexIteratorFactory;
    }

    /**
     * Get list of PHP files
     *
     * @return array
     * @throws \Exception
     */
    public function getPhpFiles()
    {
        $files = array_merge(
            $this->fileUtilities->getPhpFiles(
                Files::INCLUDE_APP_CODE
                | Files::INCLUDE_PUB_CODE
                | Files::INCLUDE_LIBS
                | Files::INCLUDE_TEMPLATES
                | Files::INCLUDE_TESTS
                | Files::INCLUDE_NON_CLASSES
            ),
            $this->getSetupPhpFiles()
        );
        return Files::composeDataSets($files);
    }

    /**
     * Get list of PHP files in setup application
     *
     * @param int $flags
     * @return array
     */
    private function getSetupPhpFiles()
    {
        $files = [];
        $regexIterator = $this->regexIteratorFactory->create(
            BP . '/setup',
            '/.*php^/'
        );
        foreach ($regexIterator as $file) {
            $files = array_merge($files, [$file]);
        }
        return $files;
    }
}
