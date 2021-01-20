<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Frontend;

use Magento\Eav\Model\Entity\Attribute\Frontend\Datetime;

class DatetimeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $localeDateMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $booleanFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $attributeMock;

    /**
     * @var Datetime
     */
    private $model;

    protected function setUp(): void
    {
        $this->booleanFactoryMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory::class);
        $this->localeDateMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->attributeMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['getAttributeCode', 'getFrontendLabel', 'getData']
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

        $this->assertInstanceOf(\Magento\Framework\Phrase::class, $this->model->getLocalizedLabel());
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
