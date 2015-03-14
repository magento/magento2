<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Frontend;

use Magento\Eav\Model\Entity\Attribute\Frontend\Datetime;

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

        $this->model = new \Magento\Eav\Model\Entity\Attribute\Frontend\Datetime($this->booleanFactoryMock, $this->localeDateMock);
        $this->model->setAttribute($this->attributeMock);
    }

    public function testGetValue()
    {
        $attributeValue = '11-11-2011';
        $date = new \DateTime($attributeValue);
        $object = new \Magento\Framework\Object(['datetime' => $attributeValue]);
        $this->attributeMock->expects($this->any())->method('getData')->with('frontend_input')
            ->will($this->returnValue('text'));

        $this->localeDateMock->expects($this->once())->method('formatDateTime')
            ->with($date, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE, null, null, null)
            ->willReturn($attributeValue);

        $this->assertEquals($attributeValue, $this->model->getValue($object));
    }
}
