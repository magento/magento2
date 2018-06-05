<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

/**
 * AbstractMergedConfigTest can be used to test merging of config files
 */
abstract class AbstractMergedConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * attributes represent merging rules
     * copied from original namespace Magento\Framework\App\Route\Config;
     *
     * class Reader
     *
     * @returns array
     */
    abstract protected function getIdAttributes();

    /**
     * Path to tough XSD for merged file validation
     *
     * @returns string
     */
    abstract protected function getMergedSchemaFile();

    /**
     * Returns an array of config files to test
     *
     * @return array
     */
    abstract protected function getConfigFiles();

    public function testMergedConfigFiles()
    {
        $invalidFiles = [];

        $files = $this->getConfigFiles();
        $validationStateMock = $this->getMock('\Magento\Framework\Config\ValidationStateInterface', [], [], '', false);
        $validationStateMock->method('isValidationRequired')
            ->willReturn(false);
        $mergedConfig = new \Magento\Framework\Config\Dom(
            '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></config>',
            $validationStateMock,
            $this->getIdAttributes()
        );

        foreach ($files as $file) {
            $content = file_get_contents($file[0]);
            try {
                $validationStateMock = $this->getMock(
                    '\Magento\Framework\Config\ValidationStateInterface',
                    [],
                    [],
                    '',
                    false
                );
                $validationStateMock->method('isValidationRequired')
                    ->willReturn(true);
                new \Magento\Framework\Config\Dom($content, $validationStateMock, $this->getIdAttributes());
                //merge won't be performed if file is invalid because of exception thrown
                $mergedConfig->merge($content);
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                $invalidFiles[] = $file[0];
            }
        }

        if (!empty($invalidFiles)) {
            $this->fail('Found broken files: ' . implode("\n", $invalidFiles));
        }

        $errors = [];
        $mergedConfig->validate($this->getMergedSchemaFile(), $errors);
        if ($errors) {
            $this->fail('Merged routes config is invalid: ' . "\n" . implode("\n", $errors));
        }
    }
}
