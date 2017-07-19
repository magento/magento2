<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Block\Element\Weee;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight
     */
    protected $model;

    public function testGetEscapedValue()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $inputValue = [
            ['value' => '30000.4'],
        ];

        $collectionFactory = $this->getMock(
            \Magento\Framework\Data\Form\Element\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $storeManager = $this->getMock(
            \Magento\Store\Model\StoreManager::class,
            [],
            [],
            '',
            false
        );

        $localeCurrency = $this->getMock(
            \Magento\Framework\Locale\Currency::class,
            [],
            [],
            '',
            false
        );
        
        $currency = $this->getMock(
            \Magento\Framework\Currency::class,
            [],
            [],
            '',
            false
        );

        $currency->expects(
            $this->any()
        )->method(
            'toCurrency'
        )->willReturn('30.000,40');

        $localeCurrency->expects(
            $this->any()
        )->method(
            'getCurrency'
        )->willReturn($currency);

        $store = $this->getMock(
            \Magento\Store\Model\Store::class,
            [],
            [],
            '',
            false
        );

        $storeManager->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn($store);

        $factory = $this->getMock(\Magento\Framework\Data\Form\Element\Factory::class, [], [], '', false);

        $this->model = $objectManager->getObject(
            \Magento\Weee\Block\Element\Weee\Tax::class,
            [
                'factoryElement' => $factory,
                'factoryCollection' => $collectionFactory,
                'storeManager' => $storeManager,
                'localeCurrency' => $localeCurrency
            ]
        );

        $this->model->setValue($inputValue);
        $this->model->setEntityAttribute(new \Magento\Framework\DataObject(['store_id' => 1]));

        $return = $this->model->getEscapedValue();
        $this->assertEquals(
            [
                ['value' => '30.000,40'],
            ],
            $return
        );
    }
}
