<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Locator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\RegistryLocator;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class RegistryLocatorTest
 */
class RegistryLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var RegistryLocator
     */
    protected $model;

    /**
     * @var Registry
     */
    protected $registryMock;

    /**
     * @var ProductInterface
     */
    protected $productMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->setMethods(['registry'])
            ->getMock();
        $this->productMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->getMockForAbstractClass();

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->willReturn($this->productMock);

        $this->model = $this->objectManager->getObject('Magento\Catalog\Model\Locator\RegistryLocator', [
            'registry' => $this->registryMock,
        ]);
    }

    public function testGetProduct()
    {
        $this->assertInstanceOf('Magento\Catalog\Api\Data\ProductInterface', $this->model->getProduct());
    }
}
