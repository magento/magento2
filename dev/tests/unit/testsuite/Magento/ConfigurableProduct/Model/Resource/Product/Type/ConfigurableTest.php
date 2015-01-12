<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\Resource\Product\Type;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Relation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $relation;

    protected function setUp()
    {
        $adapter = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')->getMock();

        $this->resource = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->resource->expects($this->any())->method('getConnection')->will($this->returnValue($adapter));
        $this->relation = $this->getMock('Magento\Catalog\Model\Resource\Product\Relation', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->configurable = $this->objectManagerHelper->getObject(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable',
            [
                'resource' => $this->resource,
                'catalogProductRelation' => $this->relation
            ]
        );
    }

    public function testSaveProducts()
    {
        $mainProduct = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getIsDuplicate', '__sleep', '__wakeup', 'getTypeInstance', '_getWriteAdapter'])
            ->disableOriginalConstructor()
            ->getMock();
        $mainProduct->expects($this->once())->method('getIsDuplicate')->will($this->returnValue(false));

        $typeInstance = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
            ->disableOriginalConstructor()->getMock();
        $typeInstance->expects($this->once())->method('getUsedProductIds')->will($this->returnValue([1]));

        $mainProduct->expects($this->once())->method('getTypeInstance')->will($this->returnValue($typeInstance));

        $this->configurable->saveProducts($mainProduct, [1, 2, 3]);
    }

    public function testSaveProductsForDuplicate()
    {
        $mainProduct = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getIsDuplicate', '__sleep', '__wakeup', 'getTypeInstance', '_getWriteAdapter'])
            ->disableOriginalConstructor()
            ->getMock();

        $mainProduct->expects($this->once())->method('getIsDuplicate')->will($this->returnValue(true));
        $mainProduct->expects($this->never())->method('getTypeInstance')->will($this->returnSelf());

        $this->configurable->saveProducts($mainProduct, [1, 2, 3]);
    }
}
