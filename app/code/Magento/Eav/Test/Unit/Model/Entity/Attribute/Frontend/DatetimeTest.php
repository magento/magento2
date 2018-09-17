<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
            ['getAttributeCode', 'getFrontendLabel'],
            [],
            '',
            false
        );

        $this->model = new Datetime($this->booleanFactoryMock, $this->localeDateMock);
        $this->model->setAttribute($this->attributeMock);
    }

    public function testGetValue()
    {
        $attributeValue = '11-11-2011';
        $date = new \DateTime($attributeValue);
        $object = new \Magento\Framework\DataObject(['datetime' => $attributeValue]);

        $this->attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('datetime');
        $this->attributeMock->expects($this->any())
            ->method('getData')
            ->with('frontend_input')
            ->willReturn('text');
        $this->localeDateMock->expects($this->once())
            ->method('formatDateTime')
            ->with($date, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE, null, null, null)
            ->willReturn($attributeValue);

        $this->assertEquals($attributeValue, $this->model->getValue($object));
    }

    /**
     * @param mixed $labelText
     * @param string $attributeCode
     * @param string $expectedResult
     * @dataProvider getLabelDataProvider
     */
    public function testGetLocalizedLabel($labelText, $attributeCode, $expectedResult)
    {
        $this->attributeMock->expects($this->exactly(2))
            ->method('getFrontendLabel')
            ->willReturn($labelText);
        $this->attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->assertInstanceOf('\Magento\Framework\Phrase', $this->model->getLocalizedLabel());
        $this->assertSame($expectedResult, (string)$this->model->getLocalizedLabel());
    }

    /**
     * @return array
     */
    public function getLabelDataProvider()
    {
        return [
            [null, 'test code', 'test code'],
            ['', 'test code', 'test code'],
            ['test label', 'test code', 'test label'],
        ];
    }
}
