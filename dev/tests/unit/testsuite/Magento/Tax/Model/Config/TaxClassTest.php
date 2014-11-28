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

/**
 * Test class for \Magento\Tax\Model\Config\TaxClass
 */
namespace Magento\Tax\Model\Config;

use Magento\TestFramework\Helper\ObjectManager;
class TaxClassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the afterSave method indirectly
     */
    public function testAfterSave()
    {
        $attributeMock = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['loadByCode', 'getId', 'setData', 'save', '__wakeup'])
            ->getMock();
        $attributeMock
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $attributeFactoryMock = $this->getMockBuilder('\Magento\Eav\Model\Entity\AttributeFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create', '__wakeup'])
            ->getMock();
        $attributeFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($attributeMock));

        $resourceMock = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['beginTransaction', '_construct', 'getIdFieldName', 'addCommitCallback', 'commit',
                          'save', '__wakeup'])
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
            'Magento\Tax\Model\Config\TaxClass',
            [
                'resource' => $resourceMock,
                'attributeFactory' => $attributeFactoryMock
            ]
        );

        $taxClass->setDataChanges(true);

        // Save the tax config data which will call _aftersave() in tax and update the default product tax class
        // No assertion should be thrown
        $taxClass->save();
    }
}
