<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Radios;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\Format;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WeightTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Weight
     */
    protected $_model;

    /**
     * @var Radios|MockObject
     */
    protected $weightSwitcher;

    /**
     * @var Factory|MockObject
     */
    protected $factory;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactory;

    /**
     * @var Format|MockObject
     */
    protected $localeFormat;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    /**
     * @var Data|MockObject
     */
    protected $directoryHelper;

    /**
     * @var SecureHtmlRenderer|MockObject
     */
    protected $secureRenderer;

    protected function setUp(): void
    {
        // Create minimal ObjectManager mock
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        AppObjectManager::setInstance($objectManagerMock);

        $this->weightSwitcher = $this->createPartialMock(Radios::class, ['__call']);
        $this->weightSwitcher->method('__call')->willReturnSelf();

        $this->factory = $this->createMock(Factory::class);
        $this->factory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'radios'
        )->willReturn(
            $this->weightSwitcher
        );
        $this->localeFormat = $this->createMock(Format::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->directoryHelper = $this->createMock(Data::class);
        $this->secureRenderer = $this->createMock(SecureHtmlRenderer::class);

        $this->collectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        // Instantiate block directly with all required constructor arguments
        $this->_model = new Weight(
            $this->factory,
            $this->collectionFactory,
            $this->escaper,
            $this->localeFormat,
            $this->directoryHelper,
            [],
            $this->secureRenderer
        );
    }

    public function testSetForm()
    {
        $form = $this->createMock(Form::class);
        // The anonymous class already returns $this for setForm, so no need for expectations
        $this->_model->setForm($form);
    }

    public function testGetEscapedValue()
    {
        $this->localeFormat->method(
            'getPriceFormat'
        )->willReturn([
            'precision' => 2,
            'decimalSymbol' => ',',
            'groupSymbol' => '.',
        ]);

        $this->_model->setValue(30000.4);
        $this->_model->setEntityAttribute(true);

        $return = $this->_model->getEscapedValue('30000.4');
        $this->assertEquals('30.000,40', $return);
    }
}
