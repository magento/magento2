<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
/**
 * @codingStandardsIgnoreStart
 */
class ConfigDomMock extends TestCase
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

    /**
     * @return string
     */
    public function getDom()
    {
        return 'reader dom result';
    }
}
