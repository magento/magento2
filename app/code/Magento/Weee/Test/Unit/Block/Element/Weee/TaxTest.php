<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Block\Element\Weee;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\Currency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Weee\Block\Element\Weee\Tax;
use PHPUnit\Framework\TestCase;

class TaxTest extends TestCase
{
    /**
     * @var Weight
     */
    protected $model;

    public function testGetEscapedValue()
    {
        $objectManager = new ObjectManager($this);

        $inputValue = [
            ['value' => '30000.4'],
        ];

        $collectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $storeManager = $this->createMock(StoreManager::class);

        $localeCurrency = $this->createMock(Currency::class);

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

        $store = $this->createMock(Store::class);

        $storeManager->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn($store);

        $factory = $this->createMock(Factory::class);

        $this->model = $objectManager->getObject(
            Tax::class,
            [
                'factoryElement' => $factory,
                'factoryCollection' => $collectionFactory,
                'storeManager' => $storeManager,
                'localeCurrency' => $localeCurrency
            ]
        );

        $this->model->setValue($inputValue);
        $this->model->setEntityAttribute(new DataObject(['store_id' => 1]));

        $return = $this->model->getEscapedValue();
        $this->assertEquals(
            [
                ['value' => '30.000,40'],
            ],
            $return
        );
    }
}
