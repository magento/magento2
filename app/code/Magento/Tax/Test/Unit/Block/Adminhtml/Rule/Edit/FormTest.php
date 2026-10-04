<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Block\Adminhtml\Rule\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Block\Adminhtml\Rule\Edit\Form;
use Magento\Tax\Model\Rate\Source;
use Magento\Tax\Model\TaxClass\Source\Customer;
use Magento\Tax\Model\TaxClass\Source\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Tax Rule Edit Form
 *
 * Class FormTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends TestCase
{
    /**
     * @var Form
     */
    private $form;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var FormFactory|MockObject
     */
    private $formFactory;

    /**
     * @var Source|MockObject
     */
    private $rateSource;

    /**
     * @var TaxRuleRepositoryInterface|MockObject
     */
    private $taxRuleRepository;

    /**
     * @var TaxClassRepositoryInterface|MockObject
     */
    private $taxClassRepository;

    /**
     * @var Customer|MockObject
     */
    private $taxClassCustomer;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    protected function setUp(): void
    {
        // Mock ObjectManager to prevent initialization errors
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        AppObjectManager::setInstance($objectManagerMock);

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

        $this->taxRuleRepository = $this->createMock(TaxRuleRepositoryInterface::class);

        $this->taxClassRepository = $this->createMock(TaxClassRepositoryInterface::class);

        $this->taxClassCustomer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->createMock(UrlInterface::class);

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
    public function testTaxRatesPageUrl(): void
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
     */
    #[DataProvider('formValuesDataProvider')]
    public function testTaxRatesSelectConfig(array $formValue, array $expected): void
    {
        $config = $this->form->getTaxRatesSelectConfig($formValue);

        $this->assertArrayHasKey('is_entity_editable', $config);
        $this->assertArrayHasKey('selected_values', $config);
        $this->assertEquals($expected, $config['selected_values']);
    }

    /**
     * Provider of form values and config data expectations.
     *
     * @return array<int, array<int, array<int, int|string>|null>>
     */
    public static function formValuesDataProvider(): array
    {
        return [
            [['tax_rate' => [1, 2, 3]], [1, 2, 3]],
            [['tax_rate' => []], []],
            [['tax_rate' => null], []]
        ];
    }
}
