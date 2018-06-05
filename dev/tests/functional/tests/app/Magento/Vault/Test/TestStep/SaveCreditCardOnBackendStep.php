<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Payment\Test\Fixture\CreditCard;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;

/**
 * Class SaveCreditCardOnBackendStep
 */
class SaveCreditCardOnBackendStep implements TestStepInterface
{
    /**
     * @var OrderCreateIndex
     */
    private $orderCreatePage;

    /**
     * Payment information.
     *
     * @var array
     */
    private $payment;

    /**
     * Credit card information.
     *
     * @var CreditCard
     */
    private $creditCard;

    /**
     * Store credit card in Vault
     *
     * @var string
     */
    private $creditCardSave;

    /**
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $payment
     * @param FixtureFactory $fixtureFactory
     * @param $creditCardClass
     * @param array $creditCard
     * @param string $creditCardSave
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        array $payment,
        FixtureFactory $fixtureFactory,
        $creditCardClass,
        array $creditCard,
        $creditCardSave = 'No'
    ) {
        $this->orderCreatePage = $orderCreateIndex;
        $this->payment = $payment;
        $this->creditCard = $fixtureFactory->createByCode($creditCardClass, ['dataset' => $creditCard['dataset']]);
        $this->creditCardSave = $creditCardSave;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $block = $this->orderCreatePage->getCreateBlock();
        $block->selectPaymentMethod($this->payment, $this->creditCard);
        $block->saveCreditCard($this->payment['method'], $this->creditCardSave);
    }
}
