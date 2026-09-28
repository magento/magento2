<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Frontend;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Frontend\Datetime;
use Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class DatetimeTest extends TestCase
{
    use MockCreationTrait;

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
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager();

        $this->booleanFactoryMock = $this->createMock(BooleanFactory::class);
        $this->localeDateMock = $this->createMock(TimezoneInterface::class);
        $this->attributeMock = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['getFrontendLabel', 'getAttributeCode', 'getFrontendInput']
        );

        $this->model = new Datetime($this->booleanFactoryMock, $this->localeDateMock);
        $this->model->setAttribute($this->attributeMock);
    }

    /**
     * Test to retrieve attribute value
     *
     * @param string $frontendInput
     * @param int $timeType
     */
    #[DataProvider('getValueDataProvider')]
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
    public static function getValueDataProvider(): array
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
     */
    #[DataProvider('getLabelDataProvider')]
    public function testGetLocalizedLabel($labelText, $attributeCode, $expectedResult)
    {
        $this->attributeMock->expects($this->exactly(2))
            ->method('getFrontendLabel')
            ->willReturn($labelText);
        $this->attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->assertInstanceOf(Phrase::class, $this->model->getLocalizedLabel());
        $this->assertSame($expectedResult, (string)$this->model->getLocalizedLabel());
    }

    /**
     * @return array
     */
    public static function getLabelDataProvider()
    {
        return [
            [null, 'test code', 'test code'],
            ['', 'test code', 'test code'],
            ['test label', 'test code', 'test label'],
        ];
    }
}
