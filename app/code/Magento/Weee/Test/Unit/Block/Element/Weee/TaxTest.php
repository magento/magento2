<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Block\Element\Weee;

class TaxTest extends \PHPUnit\Framework\TestCase
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

        $collectionFactory = $this->createPartialMock(
            \Magento\Framework\Data\Form\Element\CollectionFactory::class,
            ['create']
        );

        $storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);

        $localeCurrency = $this->createMock(\Magento\Framework\Locale\Currency::class);

        $currency = $this->createMock(\Magento\Framework\Currency::class);

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

        $store = $this->createMock(\Magento\Store\Model\Store::class);

        $storeManager->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn($store);

        $factory = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);

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
