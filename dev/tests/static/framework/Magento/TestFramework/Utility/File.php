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
    /**@#+
     * File types offset flags
     */
    const INCLUDE_APP_CODE = Files::INCLUDE_APP_CODE;
    const INCLUDE_PUB_CODE = Files::INCLUDE_PUB_CODE;
    const INCLUDE_LIBS = Files::INCLUDE_LIBS;
    const INCLUDE_TEMPLATES = Files::INCLUDE_TEMPLATES;
    const INCLUDE_TESTS = Files::INCLUDE_TESTS;
    const INCLUDE_SETUP = 128;
    const INCLUDE_NON_CLASSES = Files::INCLUDE_NON_CLASSES;
    const AS_DATA_SET = Files::AS_DATA_SET;
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
     * Get list of existing PHP files
     *
     * @param int $flags
     * @return array
     * @throws \Exception
     */
    public function getPhpFiles(
        $flags = self::INCLUDE_APP_CODE
        | self::INCLUDE_PUB_CODE
        | self::INCLUDE_LIBS
        | self::INCLUDE_TEMPLATES
        | self::INCLUDE_TESTS
        | self::INCLUDE_SETUP
        | self::INCLUDE_NON_CLASSES
        | self::AS_DATA_SET
    ) {
        $files = array_merge(
            $this->fileUtilities->getPhpFiles((2147483647 - self::AS_DATA_SET) & $flags),
            $this->getSetupPhpFiles($flags)
        );

        if ($flags & self::AS_DATA_SET) {
            return Files::composeDataSets($files);
        }
        return $files;
    }

    /**
     * Get list of PHP files in setup application
     *
     * @param int $flags
     * @return array
     */
    private function getSetupPhpFiles($flags)
    {
        $files = [];
        if ($flags & self::INCLUDE_SETUP) {
            $regexIterator = $this->regexIteratorFactory->create(
                BP . '/setup',
                '/.*php^/'
            );
            foreach ($regexIterator as $file) {
                $files = array_merge($files, [$file]);
            }
        }
        return $files;
    }
}
