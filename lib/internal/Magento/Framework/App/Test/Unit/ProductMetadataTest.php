<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProductMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    protected function setUp()
    {
        $composerJsonFinder = $this->getMockBuilder('Magento\Framework\Composer\ComposerJsonFinder')
            ->disableOriginalConstructor()->setMethods(['findComposerJson'])->getMock();
        $composerJsonFinder->expects($this->any())->method('findComposerJson')
            ->willReturn(realpath(__DIR__ . '/_files/test.composer.json'));

        $objectManager = new ObjectManager($this);
        $this->productMetadata = $objectManager->getObject(
            'Magento\Framework\App\ProductMetadata',
            ['composerJsonFinder' => $composerJsonFinder]
        );
    }

    public function testGetVersion()
    {
        $productVersion = $this->productMetadata->getVersion();
        $this->assertNotEmpty($productVersion, 'Empty product version');
        preg_match('/^([0-9\.]+)/', $productVersion, $matches);
        $this->assertArrayHasKey(1, $matches, 'Invalid product version');
        $this->assertNotEmpty($matches, 'Empty product version');
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
