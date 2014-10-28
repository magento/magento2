<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array('modelProduct' => $productMock, 'data' => array('id' => 1))
        );
        $this->assertTrue($model->isAssignedToObjects());
    }
}
