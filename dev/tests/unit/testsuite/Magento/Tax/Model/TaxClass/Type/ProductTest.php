<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tax\Model\TaxClass\Type;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAssignedToObjects()
    {
        $collectionMock = $this->getMockBuilder('Magento\Framework\Model\Resource\Db\Collection\AbstractCollection')
            ->setMethods(['addAttributeToFilter', 'getSize'])->disableOriginalConstructor()->getMock();
        $collectionMock->expects($this->once())->method('addAttributeToFilter')
            ->with($this->equalTo('tax_class_id'), $this->equalTo(1))->will($this->returnSelf());
        $collectionMock->expects($this->once())->method('getSize')
            ->will($this->returnValue(1));

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getCollection', '__wakeup', 'getEntityId'])
            ->disableOriginalConstructor()->getMock();
        $productMock->expects($this->once())->method('getCollection')->will($this->returnValue($collectionMock));

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var $model \Magento\Tax\Model\TaxClass\Type\Product */
        $model = $objectManagerHelper->getObject(
            'Magento\Tax\Model\TaxClass\Type\Product',
            ['modelProduct' => $productMock, 'data' => ['id' => 1]]
        );
        $this->assertTrue($model->isAssignedToObjects());
    }
}
