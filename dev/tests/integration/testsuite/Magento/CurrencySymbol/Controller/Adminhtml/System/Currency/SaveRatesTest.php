<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Controller\Adminhtml\System\Currency;

class SaveRatesTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{

    /** @var \Magento\Directory\Model\Currency $currencyRate */
    protected $currencyRate;

    /**
     * Initial setup
     */
    protected function setUp()
    {
        $this->currencyRate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Directory\Model\Currency::class
        );
        parent::setUp();
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        $this->_model = null;
        parent::tearDown();
    }

    /**
     * Test save action
     *
     * @magentoDbIsolation enabled
     */
    public function testSaveAction()
    {
        $currencyCode = 'USD';
        $currencyTo = 'USD';
        $rate = 1.0000;

        $request = $this->getRequest();
        $request->setPostValue(
            'rate',
            [
                $currencyCode => [$currencyTo => $rate]
            ]
        );
        $this->dispatch('backend/admin/system_currency/saveRates');

        $this->assertSessionMessages(
            $this->contains((string)__('All valid rates have been saved.')),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );

        $this->assertEquals(
            $rate,
            $this->currencyRate->load($currencyCode)->getRate($currencyTo),
            'Currency rate has not been saved'
        );
    }

    /**
     * Test save action with warning
     *
     * @magentoDbIsolation enabled
     */
    public function testSaveWithWarningAction()
    {
        $currencyCode = 'USD';
        $currencyTo = 'USD';
        $rate = '0';

        $request = $this->getRequest();
        $request->setPostValue(
            'rate',
            [
                $currencyCode => [$currencyTo => $rate]
            ]
        );
        $this->dispatch('backend/admin/system_currency/saveRates');

        $this->assertSessionMessages(
            $this->contains(
                (string)__('Please correct the input data for "%1 => %2" rate.', $currencyCode, $currencyTo)
            ),
            \Magento\Framework\Message\MessageInterface::TYPE_WARNING
        );
    }
}
