<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\ProductMetadata;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProductMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductMetadata
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInformationMock;

    protected function setUp()
    {
        $this->composerInformationMock = $this->getMockBuilder(\Magento\Framework\Composer\ComposerInformation::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new ObjectManager($this);
        $this->productMetadata = $objectManager->getObject(ProductMetadata::class);
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
        $productVersion = $this->productMetadata->getVersion();
        $this->assertNotEmpty($productVersion, 'Empty product version');
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
