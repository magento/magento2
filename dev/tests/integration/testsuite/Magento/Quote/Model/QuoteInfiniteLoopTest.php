<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteInfiniteLoopTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\TestModuleQuoteTotalsObserver\Model\Config
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->config = $this->objectManager->get(\Magento\TestModuleQuoteTotalsObserver\Model\Config::class);
        $this->config->disableObserver();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->config->disableObserver();
        $this->objectManager->removeSharedInstance(\Magento\Checkout\Model\Session::class);
    }

    /**
     * @dataProvider getLoadQuoteParametersProvider
     *
     * @param $triggerRecollect
     * @param $observerEnabled
     * @return void
     */
    public function testLoadQuoteSuccessfully($triggerRecollect, $observerEnabled): void
    {
        $originalQuote = $this->generateQuote($triggerRecollect);
        $quoteId = $originalQuote->getId();

        $this->assertGreaterThan(0, $quoteId, "The quote should have a database id");
        $this->assertEquals(
            $triggerRecollect,
            $originalQuote->getTriggerRecollect(),
            "trigger_recollect failed to be set"
        );

        if ($observerEnabled) {
            $this->config->enableObserver();
        }

        /** @var  $session \Magento\Checkout\Model\Session */
        $this->objectManager->removeSharedInstance(\Magento\Checkout\Model\Session::class);
        $session = $this->objectManager->get(\Magento\Checkout\Model\Session::class);
        $session->setQuoteId($quoteId);

        $quote = $session->getQuote();
        $this->assertEquals($quoteId, $quote->getId(), "The loaded quote should have the same ID as the initial quote");
        $this->assertEquals(0, $quote->getTriggerRecollect(), "trigger_recollect should be unset after a quote reload");
    }

    /**
     * @return array
     */
    public function getLoadQuoteParametersProvider()
    {
        return [
            [0, false],
            [0, true],
            [1, false],
            //[1, true], this combination of trigger recollect and third party code causes the loop, tested separately
        ];
    }

    /**
     *
     * @return void
     */
    public function testLoadQuoteWithTriggerRecollectInfiniteLoop(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Infinite loop detected, review the trace for the looping path');

        $originalQuote = $this->generateQuote();
        $quoteId = $originalQuote->getId();

        $this->assertGreaterThan(0, $quoteId, "The quote should have a database id");
        $this->assertEquals(1, $originalQuote->getTriggerRecollect(), "The quote has trigger_recollect set");

        // Enable an observer which gets the quote from the session
        // The observer hooks into part of the collect totals process for an easy demonstration of the loop.
        $this->config->enableObserver();

        /** @var  $session \Magento\Checkout\Model\Session */
        $this->objectManager->removeSharedInstance(\Magento\Checkout\Model\Session::class);
        $session = $this->objectManager->get(\Magento\Checkout\Model\Session::class);
        $session->setQuoteId($quoteId);
        $session->getQuote();
    }

    /**
     * Generate a quote with trigger_recollect and save it in the database.
     *
     * @param int $triggerRecollect
     * @return Quote
     */
    private function generateQuote($triggerRecollect = 1)
    {
        //Fully init a quote with standard quote session procedure
        /** @var  $session \Magento\Checkout\Model\Session */
        $session = $this->objectManager->create(\Magento\Checkout\Model\Session::class);
        $session->setQuoteId(null);
        $quote = $session->getQuote();
        $quote->setTriggerRecollect($triggerRecollect);

        /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->create('\Magento\Quote\Api\CartRepositoryInterface');
        $quoteRepository->save($quote);
        return $quote;
    }
}
