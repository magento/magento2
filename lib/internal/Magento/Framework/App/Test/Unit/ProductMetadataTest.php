<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $objectManager = new ObjectManager($this);
        $this->productMetadata = $objectManager->getObject('Magento\Framework\App\ProductMetadata');
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
