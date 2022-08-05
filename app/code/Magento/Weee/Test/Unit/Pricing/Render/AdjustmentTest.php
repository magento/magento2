<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Pricing\Render;

use Magento\Catalog\Model\Product;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Amount\Base;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\View\Element\Template\Context;
use Magento\Weee\Helper\Data;
use Magento\Weee\Model\Tax;
use Magento\Weee\Pricing\Adjustment as PricingAdjustment;
use Magento\Weee\Pricing\Render\Adjustment;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdjustmentTest extends TestCase
{
    /**
     * @var Adjustment
     */
    protected $model;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelperMock;

    /**
     * Context mock
     *
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $contextMock;

    /**
     * Price currency model mock
     *
     * @var PriceCurrency
     */
    protected $priceCurrencyMock;

    /**
     * Set up mocks for tested class
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->addMethods(['getStoreConfig'])
            ->onlyMethods(['getEventManager', 'getScopeConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrencyMock = $this->getMockForAbstractClass(
            PriceCurrencyInterface::class,
            [],
            '',
            true,
            true,
            true,
            []
        );
        $this->weeeHelperMock = $this->createMock(Data::class);
        $eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->contextMock->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($scopeConfigMock);

        $this->model = new Adjustment(
            $this->contextMock,
            $this->priceCurrencyMock,
            $this->weeeHelperMock
        );
    }

    /**
     * Test for method getAdjustmentCode
     */
    public function testGetAdjustmentCode()
    {
        $this->assertEquals(PricingAdjustment::ADJUSTMENT_CODE, $this->model->getAdjustmentCode());
    }

    /**
     * Test for method getFinalAmount
     */
    public function testGetFinalAmount()
    {
        $this->priceCurrencyMock->expects($this->once())
            ->method('format')
            ->with(10, true, 2)
            ->willReturn("$10.00");

        $displayValue = 10;
        $expectedValue = "$10.00";
        $typeOfDisplay = 1; //Just to set it to not false
        /** @var Amount $amountRender */
        $amountRender = $this->getMockBuilder(Amount::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSaleableItem', 'getDisplayValue', 'getAmount'])
            ->getMock();
        $amountRender->expects($this->any())
            ->method('getDisplayValue')
            ->willReturn($displayValue);
        $this->weeeHelperMock->expects($this->any())->method('typeOfDisplay')->willReturn($typeOfDisplay);
        /** @var Base $baseAmount */
        $baseAmount = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $amountRender->expects($this->any())
            ->method('getAmount')
            ->willReturn($baseAmount);

        $this->model->render($amountRender);
        $result = $this->model->getFinalAmount();

        $this->assertEquals($expectedValue, $result);
    }

    /**
     * Test for method showInclDescr
     *
     * @dataProvider showInclDescrDataProvider
     */
    public function testShowInclDescr($typeOfDisplay, $amount, $expectedResult)
    {
        /** @var Amount $amountRender */
        $amountRender = $this->getMockBuilder(Amount::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSaleableItem', 'getDisplayValue', 'getAmount'])
            ->getMock();
        /** @var Product $saleable */
        $saleable = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Base $baseAmount */
        $baseAmount = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();

        $baseAmount->expects($this->any())
            ->method('getValue')
            ->willReturn($amount);

        $amountRender->expects($this->any())
            ->method('getAmount')
            ->willReturn($baseAmount);

        $callback = function ($argument) use ($typeOfDisplay) {
            if (is_array($argument)) {
                return in_array($typeOfDisplay, $argument);
            } else {
                return $argument == $typeOfDisplay;
            }
        };

        $this->weeeHelperMock->expects($this->any())->method('typeOfDisplay')->willReturnCallback($callback);
        $this->weeeHelperMock->expects($this->any())->method('getAmountExclTax')->willReturn($amount);
        $amountRender->expects($this->any())->method('getSaleableItem')->willReturn($saleable);

        $this->model->render($amountRender);
        $result = $this->model->showInclDescr();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testShowInclDescr
     *
     * @return array
     */
    public function showInclDescrDataProvider()
    {
        return [
            [Tax::DISPLAY_INCL, 1.23, false],
            [Tax::DISPLAY_INCL_DESCR, 1.23, true],
            [Tax::DISPLAY_EXCL_DESCR_INCL, 1.23, false],
            [Tax::DISPLAY_EXCL, 1.23, false],
            [4, 1.23, false],
            [Tax::DISPLAY_INCL, 0, false],
            [Tax::DISPLAY_INCL_DESCR, 0, false],
            [Tax::DISPLAY_EXCL_DESCR_INCL, 0, false],
            [Tax::DISPLAY_EXCL, 0, false],
            [4, 0, false],
        ];
    }

    /**
     * Test method for showExclDescrIncl
     *
     * @param int $typeOfDisplay
     * @param float $amount
     * @param bool $expectedResult
     * @dataProvider showExclDescrInclDataProvider
     */
    public function testShowExclDescrIncl($typeOfDisplay, $amount, $expectedResult)
    {
        /** @var Amount $amountRender */
        $amountRender = $this->getMockBuilder(Amount::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSaleableItem', 'getDisplayValue', 'getAmount'])
            ->getMock();
        /** @var Product $saleable */
        $saleable = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();
        /** @var Base $baseAmount */
        $baseAmount = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $baseAmount->expects($this->any())
            ->method('getValue')
            ->willReturn($amount);
        $amountRender->expects($this->any())
            ->method('getAmount')
            ->willReturn($baseAmount);

        $callback = function ($argument) use ($typeOfDisplay) {
            if (is_array($argument)) {
                return in_array($typeOfDisplay, $argument);
            } else {
                return $argument == $typeOfDisplay;
            }
        };

        $this->weeeHelperMock->expects($this->any())->method('typeOfDisplay')->willReturnCallback($callback);
        $this->weeeHelperMock->expects($this->any())->method('getAmountExclTax')->willReturn($amount);
        $amountRender->expects($this->any())->method('getSaleableItem')->willReturn($saleable);

        $this->model->render($amountRender);
        $result = $this->model->showExclDescrIncl();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testShowExclDescrIncl
     *
     * @return array
     */
    public function showExclDescrInclDataProvider()
    {
        return [
            [Tax::DISPLAY_INCL, 1.23, false],
            [Tax::DISPLAY_INCL_DESCR, 1.23, false],
            [Tax::DISPLAY_EXCL_DESCR_INCL, 1.23, true],
            [Tax::DISPLAY_EXCL, 1.23, false],
            [4, 1.23, false],
            [Tax::DISPLAY_INCL, 0, false],
            [Tax::DISPLAY_INCL_DESCR, 0, false],
            [Tax::DISPLAY_EXCL_DESCR_INCL, 0, false],
            [Tax::DISPLAY_EXCL, 0, false],
            [4, 0, false],
        ];
    }

    /**
     * Test for method getWeeeTaxAttributes
     *
     * @param int $typeOfDisplay
     * @param array $attributes
     * @param array $expectedResult
     * @dataProvider getWeeeTaxAttributesDataProvider
     */
    public function testGetWeeeTaxAttributes($typeOfDisplay, $attributes, $expectedResult)
    {
        /** @var Amount $amountRender */
        $amountRender = $this->getMockBuilder(Amount::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSaleableItem', 'getDisplayValue', 'getAmount'])
            ->getMock();
        /** @var Product $saleable */
        $saleable = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Base $baseAmount */
        $baseAmount = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $amountRender->expects($this->any())
            ->method('getAmount')
            ->willReturn($baseAmount);
        $callback = function ($argument) use ($typeOfDisplay) {
            if (is_array($argument)) {
                return in_array($typeOfDisplay, $argument);
            } else {
                return $argument == $typeOfDisplay;
            }
        };
        $this->weeeHelperMock->expects($this->any())->method('typeOfDisplay')->willReturnCallback($callback);
        $this->weeeHelperMock->expects($this->any())
            ->method('getProductWeeeAttributesForDisplay')
            ->willReturn($attributes);
        $amountRender->expects($this->any())->method('getSaleableItem')->willReturn($saleable);

        $this->model->render($amountRender);
        $result = $this->model->getWeeeTaxAttributes();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testGetWeeeTaxAttributes
     *
     * @return array
     */
    public function getWeeeTaxAttributesDataProvider()
    {
        return [
            [Tax::DISPLAY_INCL, [1, 2, 3], []],
            [Tax::DISPLAY_INCL_DESCR, [1, 2, 3], [1, 2, 3]],
            [Tax::DISPLAY_EXCL_DESCR_INCL, [1, 2, 3], [1, 2, 3]],
            [Tax::DISPLAY_EXCL, [1, 2, 3], []],
            [4, [1, 2, 3], []],
        ];
    }

    /**
     * Test for method renderWeeeTaxAttribute
     *
     * @param DataObject $attribute
     * @param string $expectedResult
     * @dataProvider renderWeeeTaxAttributeAmountDataProvider
     */
    public function testRenderWeeeTaxAttributeAmount($attribute, $expectedResult)
    {
        $this->priceCurrencyMock->expects($this->any())->method('convertAndFormat')->willReturnArgument(0);

        $result = $this->model->renderWeeeTaxAttribute($attribute);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testRenderWeeeTaxAttributeAmount
     *
     * @return array
     */
    public function renderWeeeTaxAttributeAmountDataProvider()
    {
        return [
            [new DataObject(['amount' => 51]), 51],
            [new DataObject(['amount' => false]), false],
        ];
    }

    /**
     * Test for method renderWeeeTaxAttributeName
     *
     * @param DataObject $attribute
     * @param string $expectedResult
     * @dataProvider renderWeeeTaxAttributeNameDataProvider
     */
    public function testRenderWeeeTaxAttributeName($attribute, $expectedResult)
    {
        $this->priceCurrencyMock->expects($this->any())->method('convertAndFormat')->willReturnArgument(0);

        $result = $this->model->renderWeeeTaxAttributeName($attribute);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testRenderWeeeTaxAttributeName
     *
     * @return array
     */
    public function renderWeeeTaxAttributeNameDataProvider()
    {
        return [
            [new DataObject(['name' => 51]), 51],
            [new DataObject(['name' => false]), false],
        ];
    }

    /**
     * Test for method renderWeeeTaxAttributeWithTax
     *
     * @param DataObject $attribute
     * @param string $expectedResult
     * @dataProvider renderWeeeTaxAttributeAmountWithTaxDataProvider
     */
    public function testRenderWeeeTaxAttributeWithTax($attribute, $expectedResult)
    {
        $this->priceCurrencyMock->expects($this->any())->method('convertAndFormat')->willReturnArgument(0);

        $result = $this->model->renderWeeeTaxAttributeWithTax($attribute);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testRenderWeeeTaxAttributeAmount
     *
     * @return array
     */
    public function renderWeeeTaxAttributeAmountWithTaxDataProvider()
    {
        return [
            [new DataObject(['amount_excl_tax' => 50, 'tax_amount' => 5]), 55],
            [new DataObject(['amount_excl_tax' => false]), false],
        ];
    }
}
