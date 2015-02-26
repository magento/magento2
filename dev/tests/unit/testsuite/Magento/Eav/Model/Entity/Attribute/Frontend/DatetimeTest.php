<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $dateFormat = 'dd-MM-yyyy';
        $object = new \Magento\Framework\Object(['datetime' => $attributeValue]);
        $this->attributeMock->expects($this->any())->method('getData')->with('frontend_input')
            ->will($this->returnValue('text'));

        $this->localeDateMock->expects($this->once())->method('getDateFormat')
            ->with(\IntlDateFormatter::MEDIUM)
            ->will($this->returnValue($dateFormat));
        $this->localeDateMock->expects($this->once())->method('date')
            ->with($attributeValue)
            ->willReturn(new \DateTime($attributeValue));

        $this->assertEquals($attributeValue, $this->model->getValue($object));
    }
}
