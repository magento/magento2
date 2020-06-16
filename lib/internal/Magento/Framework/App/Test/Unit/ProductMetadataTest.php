<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductMetadataTest extends TestCase
{
    /**
     * @var ProductMetadata
     */
    private $productMetadata;

    /**
     * @var ComposerInformation|MockObject
     */
    private $composerInformationMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    protected function setUp(): void
    {
        $this->composerInformationMock = $this->getMockBuilder(ComposerInformation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->productMetadata = $objectManager->getObject(ProductMetadata::class, ['cache' => $this->cacheMock]);
        $reflectionProperty = new \ReflectionProperty($this->productMetadata, 'composerInformation');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->productMetadata, $this->composerInformationMock);
    }

    /**
     * @param array $packageList
     * @param string $expectedVersion
     * @dataProvider testGetVersionGitInstallationDataProvider
     */
    public function testGetVersion($packageList, $expectedVersion)
    {
        $this->composerInformationMock->expects($this->any())->method('getSystemPackages')->willReturn($packageList);
        $this->cacheMock->expects($this->once())->method('save')->with($expectedVersion);
        $productVersion = $this->productMetadata->getVersion();
        $this->assertNotEmpty($productVersion, 'Empty product version');
        $this->assertEquals($expectedVersion, $productVersion);
    }

    public function testGetVersionCached()
    {
        $expectedVersion = '1.2.3';
        $this->composerInformationMock->expects($this->never())->method('getSystemPackages');
        $this->cacheMock->expects($this->once())->method('load')->willReturn($expectedVersion);
        $this->cacheMock->expects($this->never())->method('save');
        $productVersion = $this->productMetadata->getVersion();
        $this->assertEquals($expectedVersion, $productVersion);
    }

    /**
     * @return array
     */
    public function testGetVersionGitInstallationDataProvider()
    {
        return [
            [
                [
                    0 => [
                        'name'    => 'magento/product-community-edition',
                        'version' => '123.456.789'
                    ],
                    1 => [
                        'name'    => 'magento/product-other-edition',
                        'version' => '987.654.321'
                    ],
                ],
                '123.456.789'
            ],
            [
                [],
                'UNKNOWN'
            ]
        ];
    }

    public function testGetEdition()
    {
        $productEdition = $this->productMetadata->getEdition();
        $this->assertNotEmpty($productEdition, 'Empty product edition');
    }

    public function testGetName()
    {
        $productName = $this->productMetadata->getName();
        $this->assertNotEmpty($productName, 'Empty product name');
    }
}
