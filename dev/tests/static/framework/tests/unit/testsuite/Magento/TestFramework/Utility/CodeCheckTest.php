<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestFramework\Utility;

use PHPUnit\Framework\Attributes\DataProvider;

class CodeCheckTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CodeCheck
     */
    private $codeCheck;

    protected function setUp(): void
    {
        $this->codeCheck = new CodeCheck();
    }

    /**
     * @param string $fileContent
     * @param bool $isClassUsed     */
    #[DataProvider('isClassUsedDataProvider')]
    public function testIsClassUsed($fileContent, $isClassUsed)
    {
        $this->assertEquals(
            $isClassUsed,
            $this->codeCheck->isClassUsed('MyClass', $fileContent)
        );
    }

    /**
     * @return array
     */
    public static function isClassUsedDataProvider()
    {
        return [
            [file_get_contents(__DIR__ . '/_files/create_new_instance.txt'), true],
            [file_get_contents(__DIR__ . '/_files/create_new_instance2.txt'), true],
            [file_get_contents(__DIR__ . '/_files/create_new_instance3.txt'), true],
            [file_get_contents(__DIR__ . '/_files/class_name_in_namespace_and_variable_name.txt'), false],
            [file_get_contents(__DIR__ . '/_files/extends.txt'), true],
            [file_get_contents(__DIR__ . '/_files/extends2.txt'), true],
            [file_get_contents(__DIR__ . '/_files/use.txt'), true],
            [file_get_contents(__DIR__ . '/_files/use2.txt'), true]
        ];
    }

    /**
     * @param string $fileContent
     * @param bool $isDirectDescendant
     */
    #[DataProvider('isDirectDescendantDataProvider')]
    public function testIsDirectDescendant($fileContent, $isDirectDescendant)
    {
        $this->assertEquals(
            $isDirectDescendant,
            $this->codeCheck->isDirectDescendant($fileContent, 'MyClass')
        );
    }

    /**
     * @return array
     */
    public static function isDirectDescendantDataProvider()
    {
        return [
            [file_get_contents(__DIR__ . '/_files/extends.txt'), true],
            [file_get_contents(__DIR__ . '/_files/extends2.txt'), true],
            [file_get_contents(__DIR__ . '/_files/implements.txt'), true],
            [file_get_contents(__DIR__ . '/_files/implements2.txt'), true]
        ];
    }
}
