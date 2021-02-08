<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Block\Adminhtml\Rule\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Block\Adminhtml\Rule\Edit\Form;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Model\Rate\Source;
use Magento\Tax\Model\TaxClass\Source\Customer;
use Magento\Tax\Model\TaxClass\Source\Product;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Test for Tax Rule Edit Form
 *
 * Class FormTest
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Form
     */
    private $form;

    /**
     * @var Context | MockObject
     */
    private $context;

    /**
     * @var Registry | MockObject
     */
    private $registry;

    /**
     * @var FormFactory | MockObject
     */
    private $formFactory;

    /**
     * @var Source | MockObject
     */
    private $rateSource;

    /**
     * @var TaxRuleRepositoryInterface | MockObject
     */
    private $taxRuleRepository;

    /**
     * @var TaxClassRepositoryInterface | MockObject
     */
    private $taxClassRepository;

    /**
     * @var Customer | MockObject
     */
    private $taxClassCustomer;

    /**
     * @var Product | MockObject
     */
    private $product;

    /**
     * @var UrlInterface | MockObject
     */
    private $urlBuilder;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formFactory = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rateSource = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxRuleRepository = $this->getMockBuilder(TaxRuleRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->taxClassRepository = $this->getMockBuilder(TaxClassRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->taxClassCustomer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->form = $objectManagerHelper->getObject(Form::class, [
            'context' => $this->context,
            'registry' => $this->registry,
            'formFactory' => $this->formFactory,
            'rateSource' => $this->rateSource,
            'ruleService' => $this->taxRuleRepository,
            'taxClassService' => $this->taxClassRepository,
            'customerTaxClassSource' => $this->taxClassCustomer,
            'productTaxClassSource' => $this->product,
            '_urlBuilder' => $this->urlBuilder
        ]);
    }

    /**
     * Check tax lazy loading URL.
     *
     * @see \Magento\Tax\Block\Adminhtml\Rule\Edit\Form::getTaxRatesPageUrl
     */
    public function testTaxRatesPageUrl()
    {
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('tax/rule/ajaxLoadRates/')
            ->willReturn('some_url');

        $this->assertEquals('some_url', $this->form->getTaxRatesPageUrl());
    }

    /**
     * Check tax lazy loading URL.
     *
     * @param array $formValue
     * @param array $expected
     * @see \Magento\Tax\Block\Adminhtml\Rule\Edit\Form::getTaxRatesSelectConfig
     * @dataProvider formValuesDataProvider
     */
    public function testTaxRatesSelectConfig($formValue, $expected)
    {
        $config = $this->form->getTaxRatesSelectConfig($formValue);

        $this->assertArrayHasKey('is_entity_editable', $config);
        $this->assertArrayHasKey('selected_values', $config);
        $this->assertEquals($expected, $config['selected_values']);
    }

    /**
     * Provider of form values and config data expectations.
     *
     * @return array
     */
    public function formValuesDataProvider()
    {
        return [
            [['tax_rate' => [1, 2, 3]], [1, 2, 3]],
            [['tax_rate' => []], []],
            [['tax_rate' => null], []]
        ];
    }
}
