<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Frontend;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Frontend\Datetime;
use Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test Entity datetime frontend attribute
 */
class DatetimeTest extends TestCase
{
    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDateMock;

    /**
     * @var BooleanFactory|MockObject
     */
    private $booleanFactoryMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $attributeMock;

    /**
     * @var Datetime
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->booleanFactoryMock = $this->createMock(BooleanFactory::class);
        $this->localeDateMock = $this->createMock(TimezoneInterface::class);
        $this->attributeMock = $this->createPartialMock(
            AbstractAttribute::class,
            ['getAttributeCode', 'getFrontendLabel', 'getFrontendInput']
        );

        $this->model = new Datetime($this->booleanFactoryMock, $this->localeDateMock);
        $this->model->setAttribute($this->attributeMock);
    }

    /**
     * Test to retrieve attribute value
     *
     * @param string $frontendInput
     * @param int $timeType
     * @dataProvider getValueDataProvider
     */
    public function testGetValue(string $frontendInput, int $timeType)
    {
        $attributeValue = '11-11-2011';
        $attributeCode = 'datetime';
        $date = new \DateTime($attributeValue);
        $object = new DataObject([$attributeCode => $attributeValue]);

        $this->attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->attributeMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn($frontendInput);
        $this->localeDateMock->expects($this->once())
            ->method('formatDateTime')
            ->with($date, \IntlDateFormatter::MEDIUM, $timeType)
            ->willReturn($attributeValue);

        $this->assertEquals($attributeValue, $this->model->getValue($object));
    }

    /**
     * Retrieve attribute value data provider
     *
     * @return array
     */
    public function getValueDataProvider(): array
    {
        return [
            ['frontendInput' => 'date', 'timeType' => \IntlDateFormatter::NONE],
            ['frontendInput' => 'datetime', 'timeType' => \IntlDateFormatter::MEDIUM],
        ];
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
