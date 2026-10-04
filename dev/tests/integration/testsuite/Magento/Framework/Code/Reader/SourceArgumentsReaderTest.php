<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Reader;

use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__ . '/_files/SourceArgumentsReaderTest.php.sample';

class SourceArgumentsReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Code\Reader\SourceArgumentsReader
     */
    protected $sourceArgumentsReader;

    protected function setUp(): void
    {
        $this->sourceArgumentsReader = new \Magento\Framework\Code\Reader\SourceArgumentsReader();
    }

    /**
     * @param string $class
     * @param array $expectedResult
     */
    #[DataProvider('getConstructorArgumentTypesDataProvider')]
    public function testGetConstructorArgumentTypes($class, $expectedResult)
    {
        $class = new \ReflectionClass($class);
        $actualResult = $this->sourceArgumentsReader->getConstructorArgumentTypes($class);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public static function getConstructorArgumentTypesDataProvider()
    {
        return [
            [
                'Some\Testing\Name\Space\AnotherSimpleClass',
                [
                    '\Some\Testing\Name\Space\Item',
                    '\Imported\Name\Space\One',
                    '\Imported\Name\Space\AnotherTest\Extended',
                    '\Imported\Name\Space\Test',
                    '\Imported\Name\Space\ClassName\Under\Test',
                    '\Imported\Name\Space\ClassName',
                    '\Some\Testing\Name\Space\Test',
                    '\Exception',
                    '',
                    '\Imported\Name\Space\ClassName',
                    'array',
                    ''
                ],
            ],
            [
                '\stdClass',
                [null]
            ]
        ];
    }
}
