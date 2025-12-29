<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Helper\Data;
use Magento\Integration\Model\Integration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /** @var Data */
    protected $dataHelper;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->dataHelper = $helper->getObject(Data::class);
    }

    public function testMapResources(): void
    {
        $testData = require __DIR__ . '/_files/acl.php';
        $expectedData = require __DIR__ . '/_files/acl-map.php';
        $this->assertEquals($expectedData, $this->dataHelper->mapResources($testData));
    }

    /**
     * @param array<string, mixed> $integrationsData
     * @param bool $expectedResult
     */
    #[DataProvider('integrationDataProvider')]
    public function testIsConfigType(array $integrationsData, bool $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->dataHelper->isConfigType($integrationsData));
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public static function integrationDataProvider(): array
    {
        return [
            [
                [
                    'id' => 1,
                    Integration::NAME => 'TestIntegration1',
                    Integration::EMAIL => 'test-integration1@magento.com',
                    Integration::ENDPOINT => 'http://endpoint.com',
                    Integration::SETUP_TYPE => 1,
                ],
                true,
            ],
            [
                [
                    'id' => 1,
                    Integration::NAME => 'TestIntegration1',
                    Integration::EMAIL => 'test-integration1@magento.com',
                    Integration::ENDPOINT => 'http://endpoint.com',
                    Integration::SETUP_TYPE => 0,
                ],
                false,
            ]
        ];
    }
}
