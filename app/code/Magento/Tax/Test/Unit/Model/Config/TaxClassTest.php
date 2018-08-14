<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test class for \Magento\Tax\Model\Config\TaxClass
 */
namespace Magento\Tax\Test\Unit\Model\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
class TaxClassTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the afterSave method indirectly
     */
    public function testAfterSave()
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadByCode', 'getId', 'setData', 'save', '__wakeup'])
            ->getMock();
        $attributeMock
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $attributeFactoryMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\AttributeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', '__wakeup'])
            ->getMock();
        $attributeFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($attributeMock));

        $resourceMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['beginTransaction', '_construct', 'getIdFieldName', 'addCommitCallback', 'commit',
                          'save', '__wakeup', ])
            ->getMock();
        $resourceMock
            ->expects($this->any())
            ->method('beginTransaction')
            ->will($this->returnValue(null));
        $resourceMock
            ->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('tax'));
        $resourceMock
            ->expects($this->any())
            ->method('addCommitCallback')
            ->will($this->returnValue($resourceMock));

        $objectManager = new ObjectManager($this);
        $taxClass = $objectManager->getObject(
            \Magento\Tax\Model\Config\TaxClass::class,
            [
                'resource' => $resourceMock,
                'attributeFactory' => $attributeFactoryMock
            ]
        );

        $taxClass->setDataChanges(true);

        // Save the tax config data which will call _aftersave() in tax and update the default product tax class
        // No assertion should be thrown
        $result = $taxClass->save();
        $this->assertNotNull($result);
    }
}
