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
namespace Magento\Eav\Model\Entity\Attribute\Frontend;

class DatetimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $booleanFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    /**
     * @var Datetime
     */
    private $model;

    protected function setUp()
    {
        $this->booleanFactoryMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory',
            [],
            [],
            '',
            false
        );

        $this->localeDateMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');

        $this->attributeMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            [],
            [],
            '',
            false
        );
        $this->attributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue('datetime'));

        $this->model = new Datetime($this->booleanFactoryMock, $this->localeDateMock);
        $this->model->setAttribute($this->attributeMock);
    }

    public function testGetValue()
    {
        $attributeValue = '11-11-2011';
        $dateFormat = 'dd-mm-yyyy';
        $object = new \Magento\Framework\Object(array('datetime' => $attributeValue));
        $this->attributeMock->expects($this->any())->method('getData')->with('frontend_input')
            ->will($this->returnValue('text'));

        $this->localeDateMock->expects($this->once())->method('getDateFormat')
            ->with(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM)
            ->will($this->returnValue($dateFormat));
        $dateMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\DateInterface');
        $dateMock->expects($this->once())->method('toString')->with($dateFormat)
            ->will($this->returnValue($attributeValue));
        $this->localeDateMock->expects($this->once())->method('date')
            ->with($attributeValue, \Zend_Date::ISO_8601, null, false)
            ->will($this->returnValue($dateMock));

        $this->assertEquals($attributeValue, $this->model->getValue($object));
    }

    public function testGetValueWhenDateCannotBeRepresentedUsingIso8601()
    {
        $attributeValue = '11-11-2011';
        $dateFormat = 'dd-mm-yyyy';
        $object = new \Magento\Framework\Object(array('datetime' => $attributeValue));
        $this->localeDateMock->expects($this->once())->method('getDateFormat')
            ->with(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM)
            ->will($this->returnValue($dateFormat));
        $this->attributeMock->expects($this->any())->method('getData')->with('frontend_input')
            ->will($this->returnValue('text'));

        $dateMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\DateInterface');
        $dateMock->expects($this->once())->method('toString')->with($dateFormat)
            ->will($this->returnValue($attributeValue));
        $this->localeDateMock->expects($this->at(1))->method('date')
            ->will($this->throwException(new \Exception('Wrong Date')));
        $this->localeDateMock->expects($this->at(2))->method('date')
            ->with($attributeValue, null, null, false)
            ->will($this->returnValue($dateMock));

        $this->assertEquals($attributeValue, $this->model->getValue($object));
    }
}
