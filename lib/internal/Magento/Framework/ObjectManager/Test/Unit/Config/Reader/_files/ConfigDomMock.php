<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

class ConfigDomMock extends \PHPUnit_Framework_TestCase
{
    /**
     * @param null|string $initialContents
     * @param array $idAttributes
     * @param string $typeAttribute
     * @param $perFileSchema
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct($initialContents, $validationState, $idAttributes, $typeAttribute, $perFileSchema)
    {
        $this->assertEquals('first content item', $initialContents);
        $this->assertEquals('xsi:type', $typeAttribute);
    }

    /**
     * @param $schemaFile
     * @param $errors
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate($schemaFile, $errors)
    {
        return true;
    }

    public function getDom()
    {
        return 'reader dom result';
    }
}
