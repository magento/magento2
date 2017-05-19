<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    /**@#+
     * File types offset flags
     */
    const INCLUDE_APP_CODE = Files::INCLUDE_APP_CODE;
    const INCLUDE_PUB_CODE = Files::INCLUDE_PUB_CODE;
    const INCLUDE_LIBS = Files::INCLUDE_LIBS;
    const INCLUDE_TEMPLATES = Files::INCLUDE_TEMPLATES;
    const INCLUDE_TESTS = Files::INCLUDE_TESTS;
    const INCLUDE_NON_CLASSES = Files::INCLUDE_NON_CLASSES;
    const INCLUDE_SETUP = 256;
    /**#@-*/

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
    public function getPhpFiles($flags = null)
    {
        if (!$flags) {
            $flags = Files::INCLUDE_APP_CODE
            | Files::INCLUDE_PUB_CODE
            | Files::INCLUDE_LIBS
            | Files::INCLUDE_TEMPLATES
            | Files::INCLUDE_TESTS
            | Files::INCLUDE_NON_CLASSES
            | self::INCLUDE_SETUP;
        }
        $files = $this->fileUtilities->getPhpFiles($flags);
        if ($flags & self::INCLUDE_SETUP) {
            $files = array_merge(
                $files,
                $this->getSetupPhpFiles()
            );
        }
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
            '/.*php$/'
        );
        foreach ($regexIterator as $file) {
            $files[] = $file[0];
        }
        return $files;
    }
}
