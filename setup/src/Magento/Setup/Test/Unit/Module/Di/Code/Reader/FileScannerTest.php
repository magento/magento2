<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader;

use Magento\Setup\Module\Di\Code\Reader\FileScanner;
use PHPUnit\Framework\TestCase;

class FileScannerTest extends TestCase
{
    /**
     * @var FileScanner
     */
    private $fileScanner;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fileScanner = new FileScanner(
            __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'TestClass.php'
        );
    }

    /**
     * Check that all uses are found.
     *
     * @return void
     */
    public function testGetUses(): void
    {
        $actualResult = $this->fileScanner->getUses();
        $expectedResult = $this->getExpectedResultForTestClass();

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Check that all uses are found with correct namespace provided.
     *
     * @return void
     */
    public function testGetUsesWithCorrectNamespace(): void
    {
        $actualResult = $this->fileScanner->getUses('Some\TestNamespace');
        $expectedResult = $this->getExpectedResultForTestClass();

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Check that function returns null with wrong namespace provided.
     *
     * @return void
     */
    public function testGetUsesWithAnotherNamespace(): void
    {
        $result = $this->fileScanner->getUses('Another\WrongNamespace');

        $this->assertNull($result);
    }

    /**
     * Data provider for getUses test
     *
     * @return array
     */
    private function getExpectedResultForTestClass(): array
    {
        return [
            [
                'use' => 'Some\OtherNamespace\OtherClass',
                'as' => null
            ],
            [
                'use' => 'Some\TestNamespace\TestInteface',
                'as' => 'TestAlias'
            ]
        ];
    }
}
