<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Plugin\Model;

use Magento\Catalog\Model\Config as Subject;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Plugin\Model\ConfigPlugin;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ConfigPlugin class
 */
class ConfigPluginTest extends TestCase
{
    /**
     * @var ConfigPlugin
     */
    private $plugin;

    /**
     * @var EavConfig|MockObject
     */
    private $eavConfigMock;

    /**
     * @var Subject|MockObject
     */
    private $subjectMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $specialPriceAttributeMock;

    protected function setUp(): void
    {
        $this->eavConfigMock = $this->createMock(EavConfig::class);
        $this->subjectMock = $this->createMock(Subject::class);
        $this->specialPriceAttributeMock = $this->createMock(AbstractAttribute::class);

        $this->plugin = new ConfigPlugin($this->eavConfigMock);
    }

    /**
     * Test that special_price attribute is added when not present in result
     */
    public function testAfterGetAttributesUsedInProductListingAddsSpecialPriceWhenNotPresent(): void
    {
        $existingAttributes = [
            'name' => $this->createMock(AbstractAttribute::class),
            'price' => $this->createMock(AbstractAttribute::class)
        ];

        $this->specialPriceAttributeMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'special_price')
            ->willReturn($this->specialPriceAttributeMock);

        $result = $this->plugin->afterGetAttributesUsedInProductListing(
            $this->subjectMock,
            $existingAttributes
        );

        $this->assertArrayHasKey('special_price', $result);
        $this->assertSame($this->specialPriceAttributeMock, $result['special_price']);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('price', $result);
        $this->assertCount(3, $result);
    }

    /**
     * Test that special_price attribute is not added when already present in result
     */
    public function testAfterGetAttributesUsedInProductListingDoesNotAddSpecialPriceWhenAlreadyPresent(): void
    {
        $existingAttributes = [
            'name' => $this->createMock(AbstractAttribute::class),
            'price' => $this->createMock(AbstractAttribute::class),
            'special_price' => $this->createMock(AbstractAttribute::class)
        ];

        $this->eavConfigMock->expects($this->never())
            ->method('getAttribute');

        $result = $this->plugin->afterGetAttributesUsedInProductListing(
            $this->subjectMock,
            $existingAttributes
        );

        $this->assertSame($existingAttributes, $result);
        $this->assertCount(3, $result);
    }

    /**
     * Test that special_price attribute is not added when attribute is not found
     */
    public function testAfterGetAttributesUsedInProductListingDoesNotAddSpecialPriceWhenAttributeNotFound(): void
    {
        $existingAttributes = [
            'name' => $this->createMock(AbstractAttribute::class),
            'price' => $this->createMock(AbstractAttribute::class)
        ];

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'special_price')
            ->willReturn(null);

        $result = $this->plugin->afterGetAttributesUsedInProductListing(
            $this->subjectMock,
            $existingAttributes
        );

        $this->assertArrayNotHasKey('special_price', $result);
        $this->assertSame($existingAttributes, $result);
        $this->assertCount(2, $result);
    }

    /**
     * Test that special_price attribute is not added when attribute has no ID
     */
    public function testAfterGetAttributesUsedInProductListingDoesNotAddSpecialPriceWhenAttributeHasNoId(): void
    {
        $existingAttributes = [
            'name' => $this->createMock(AbstractAttribute::class),
            'price' => $this->createMock(AbstractAttribute::class)
        ];

        $this->specialPriceAttributeMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'special_price')
            ->willReturn($this->specialPriceAttributeMock);

        $result = $this->plugin->afterGetAttributesUsedInProductListing(
            $this->subjectMock,
            $existingAttributes
        );

        $this->assertArrayNotHasKey('special_price', $result);
        $this->assertSame($existingAttributes, $result);
        $this->assertCount(2, $result);
    }

    /**
     * Test that special_price attribute is not added when attribute ID is empty string
     */
    public function testAfterGetAttributesUsedInProductListingDoesNotAddSpecialPriceWhenAttributeIdIsEmptyString(): void
    {
        $existingAttributes = [
            'name' => $this->createMock(AbstractAttribute::class),
            'price' => $this->createMock(AbstractAttribute::class)
        ];

        $this->specialPriceAttributeMock->expects($this->once())
            ->method('getId')
            ->willReturn('');

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'special_price')
            ->willReturn($this->specialPriceAttributeMock);

        $result = $this->plugin->afterGetAttributesUsedInProductListing(
            $this->subjectMock,
            $existingAttributes
        );

        $this->assertArrayNotHasKey('special_price', $result);
        $this->assertSame($existingAttributes, $result);
        $this->assertCount(2, $result);
    }

    /**
     * Test that special_price attribute is not added when attribute ID is zero
     */
    public function testAfterGetAttributesUsedInProductListingDoesNotAddSpecialPriceWhenAttributeIdIsZero(): void
    {
        $existingAttributes = [
            'name' => $this->createMock(AbstractAttribute::class),
            'price' => $this->createMock(AbstractAttribute::class)
        ];

        $this->specialPriceAttributeMock->expects($this->once())
            ->method('getId')
            ->willReturn(0);

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'special_price')
            ->willReturn($this->specialPriceAttributeMock);

        $result = $this->plugin->afterGetAttributesUsedInProductListing(
            $this->subjectMock,
            $existingAttributes
        );

        $this->assertArrayNotHasKey('special_price', $result);
        $this->assertSame($existingAttributes, $result);
        $this->assertCount(2, $result);
    }

    /**
     * Test with empty attributes array
     */
    public function testAfterGetAttributesUsedInProductListingWithEmptyAttributesArray(): void
    {
        $existingAttributes = [];

        $this->specialPriceAttributeMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'special_price')
            ->willReturn($this->specialPriceAttributeMock);

        $result = $this->plugin->afterGetAttributesUsedInProductListing(
            $this->subjectMock,
            $existingAttributes
        );

        $this->assertArrayHasKey('special_price', $result);
        $this->assertSame($this->specialPriceAttributeMock, $result['special_price']);
        $this->assertCount(1, $result);
    }

    /**
     * Test that the constant SPECIAL_PRICE_ATTR_CODE is used correctly
     */
    public function testSpecialPriceAttributeCodeConstant(): void
    {
        $reflection = new \ReflectionClass(ConfigPlugin::class);
        $constant = $reflection->getConstant('SPECIAL_PRICE_ATTR_CODE');
        
        $this->assertEquals('special_price', $constant);
    }

    /**
     * Test that the plugin uses the correct entity type
     */
    public function testUsesCorrectEntityType(): void
    {
        $existingAttributes = [];

        $this->specialPriceAttributeMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'special_price')
            ->willReturn($this->specialPriceAttributeMock);

        $this->plugin->afterGetAttributesUsedInProductListing(
            $this->subjectMock,
            $existingAttributes
        );

        // The test passes if the method is called with the correct parameters
        $this->addToAssertionCount(1);
    }
}
