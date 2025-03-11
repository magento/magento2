<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Weight;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Locale\Format;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class WeightTest extends TestCase
{
    /**
     * @var Weight
     */
    protected $model;

    protected function setUp(): void
    {
        $objectHelper = new ObjectManager($this);

        // we want to use an actual implementation of \Magento\Framework\Locale\FormatInterface
        $scopeResolver = $this->getMockForAbstractClass(
            ScopeResolverInterface::class,
            [],
            '',
            false
        );
        $localeResolver = $this->getMockForAbstractClass(
            ResolverInterface::class,
            [],
            '',
            false
        );
        $currencyFactory = $this->createMock(CurrencyFactory::class);
        $localeFormat = $objectHelper->getObject(
            Format::class,
            [
                'scopeResolver'   => $scopeResolver,
                'localeResolver'  => $localeResolver,
                'currencyFactory' => $currencyFactory,
            ]
        );

        // the model we are testing
        $this->model = $objectHelper->getObject(
            Weight::class,
            ['localeFormat' => $localeFormat]
        );

        $attribute = $this->getMockForAbstractClass(
            AbstractAttribute::class,
            [],
            '',
            false
        );
        $this->model->setAttribute($attribute);
    }

    /**
     * Tests for the cases that expect to pass validation
     *
     * @dataProvider dataProviderValidate
     */
    public function testValidate($value)
    {
        $object = $this->createMock(Product::class);
        $object->expects($this->once())->method('getData')->willReturn($value);

        $this->assertTrue($this->model->validate($object));
    }

    /**
     * @return array
     */
    public static function dataProviderValidate()
    {
        return [
            'US simple' => ['1234.56'],
            'US full'   => ['123,456.78'],
            'Brazil'    => ['123.456,78'],
            'India'     => ['1,23,456.78'],
            'Lebanon'   => ['1 234'],
            'zero'      => ['0.00'],
            'NaN becomes zero' => ['kiwi'],
        ];
    }

    /**
     * Tests for the cases that expect to fail validation
     *
     * @dataProvider dataProviderValidateForFailure
     */
    public function testValidateForFailure($value)
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $object = $this->createMock(Product::class);
        $object->expects($this->once())->method('getData')->willReturn($value);

        $this->model->validate($object);
        $this->fail('Expected the following value to NOT validate: ' . $value);
    }

    /**
     * @return array
     */
    public static function dataProviderValidateForFailure()
    {
        return [
            'negative US simple' => ['-1234.56'],
            'negative US full'   => ['-123,456.78'],
            'negative Brazil'    => ['-123.456,78'],
            'negative India'     => ['-1,23,456.78'],
            'negative Lebanon'   => ['-1 234'],
        ];
    }
}
