<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Plugin\Eav\Model\Entity\Attribute;

use Magento\Catalog\Model\Product\Attribute\OptionManagement as CatalogOptionManagement;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogAttribute;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Magento\Swatches\Plugin\Eav\Model\Entity\Attribute\OptionManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@link OptionManagement} plugin.
 *
 * @see OptionManagement
 */
class OptionManagementTest extends TestCase
{
    /**
     * @var OptionManagement
     */
    private $plugin;

    /**
     * @var MockObject|AttributeRepository
     */
    private $attributeRepositoryMock;

    /**
     * @var MockObject|SwatchHelper
     */
    private $swatchHelperMock;

    /**
     * @var MockObject|CatalogOptionManagement
     */
    private $subjectMock;

    /**
     * @var MockObject|AttributeOptionInterface
     */
    private $optionMock;

    /**
     * @var MockObject|CatalogAttribute
     */
    private $attributeMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeRepositoryMock = $this->createMock(AttributeRepository::class);
        $this->swatchHelperMock = $this->createMock(SwatchHelper::class);
        $this->subjectMock = $this->createMock(CatalogOptionManagement::class);
        $this->optionMock = $this->createMock(AttributeOptionInterface::class);
        $this->attributeMock = $this->getMockBuilder(CatalogAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new OptionManagement(
            $this->attributeRepositoryMock,
            $this->swatchHelperMock
        );
    }

    /**
     * Test beforeUpdate when the option value matches the existing option ID (no new swatch value).
     *
     * @return void
     */
    public function testBeforeUpdateWithNoNewSwatchValue(): void
    {
        $attributeCode = 'color';
        $optionId = 123;

        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->attributeMock);

        $this->swatchHelperMock->expects($this->once())
            ->method('isSwatchAttribute')
            ->willReturn(true);

        $this->optionMock->expects($this->any())
            ->method('getValue')
            ->willReturn((string)$optionId);

        $this->attributeMock->expects($this->never())
            ->method('setData');

        $this->plugin->beforeUpdate($this->subjectMock, $attributeCode, $optionId, $this->optionMock);
    }

    /**
     * Test beforeUpdate when the option value is empty.
     *
     * @return void
     */
    public function testBeforeUpdateWithEmptyValue(): void
    {
        $attributeCode = 'color';
        $optionId = 123;

        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->attributeMock);

        $this->swatchHelperMock->expects($this->once())
            ->method('isSwatchAttribute')
            ->willReturn(true);

        $this->optionMock->expects($this->any())
            ->method('getValue')
            ->willReturn('');

        $this->attributeMock->expects($this->never())
            ->method('setData');

        $this->plugin->beforeUpdate($this->subjectMock, $attributeCode, $optionId, $this->optionMock);
    }

    /**
     * Test beforeUpdate when a valid visual swatch value is provided.
     *
     * @return void
     */
    public function testBeforeUpdateWithValidSwatchValue(): void
    {
        $attributeCode = 'color';
        $optionId = 123;
        $swatchValue = '#ff0000';

        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->attributeMock);

        $this->swatchHelperMock->expects($this->once())
            ->method('isSwatchAttribute')
            ->willReturn(true);

        $this->swatchHelperMock->expects($this->once())
            ->method('isVisualSwatch')
            ->willReturn(true);

        $this->optionMock->expects($this->any())
            ->method('getValue')
            ->willReturn($swatchValue);

        $this->attributeMock->expects($this->once())
            ->method('setData')
            ->with('swatchvisual', ['value' => [(string)$optionId => $swatchValue]]);

        $this->plugin->beforeUpdate($this->subjectMock, $attributeCode, $optionId, $this->optionMock);
    }
}
