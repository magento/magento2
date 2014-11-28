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

namespace Magento\Catalog\Model\Resource;

use Magento\TestFramework\Helper\ObjectManager;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $setFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->setFactoryMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\SetFactory',
            ['create', '__wakeup'],
            [],
            '',
            false
        );
        $this->typeFactoryMock = $this->getMock(
            '\Magento\Eav\Model\Entity\TypeFactory',
            ['create', '__wakeup'],
            [],
            '',
            false
        );

        $this->model = $objectManager->getObject(
            '\Magento\Catalog\Model\Resource\Product',
            [
                'setFactory' => $this->setFactoryMock,
                'typeFactory' => $this->typeFactoryMock,
            ]
        );
    }

    public function testValidateWrongAttributeSet()
    {
        $productTypeId = 4;
        $expectedErrorMessage = ['attribute_set' => 'Invalid attribute set entity type'];

        $productMock = $this->getMock('\Magento\Framework\Object', ['getAttributeSetId', '__wakeup'], [], '', false);
        $attributeSetMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Set',
            ['load', 'getEntityTypeId', '__wakeup'],
            [],
            '',
            false
        );
        $entityTypeMock = $this->getMock('\Magento\Eav\Model\Entity\Type', [], [], '', false);

        $this->typeFactoryMock->expects($this->once())->method('create')->will($this->returnValue($entityTypeMock));
        $entityTypeMock->expects($this->once())->method('loadByCode')->with('catalog_product')->willReturnSelf();

        $productAttributeSetId = 4;
        $productMock->expects($this->once())->method('getAttributeSetId')
            ->will($this->returnValue($productAttributeSetId));

        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($attributeSetMock));
        $attributeSetMock->expects($this->once())->method('load')->with($productAttributeSetId)->willReturnSelf();

        //attribute set of wrong type
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->will($this->returnValue(3));
        $entityTypeMock->expects($this->once())->method('getId')->will($this->returnValue($productTypeId));

        $this->assertEquals($expectedErrorMessage, $this->model->validate($productMock));
    }
}
