<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Cart;

use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Mtf\Block\BlockFactory;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Block\Mapper;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Util\ModuleResolver\SequenceSorterInterface;

/**
 * Class GiftOptions
 * Add gift options on checkout cart order level
 */
class GiftOptions extends Form
{
    /**
     * Selector for gift message on order form
     *
     * @var string
     */
    protected $giftMessageOrderForm = ".gift-message fieldset";

    /**
     * Allow gift message on order level
     *
     * @var string
     */
    protected $allowGiftOptions = '.title';

    /**
     * Selector for apply Gift Message button on item
     *
     * @var string
     */
    protected $giftMessageOrderButton = ".action-update";

    /**
     * Selector for Gift Message Summary
     *
     * @var string
     */
    protected $giftMessageSummary = ".gift-message-summary";

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * @param SimpleElement $element
     * @param BlockFactory $blockFactory
     * @param Mapper $mapper
     * @param BrowserInterface $browser
     * @param SequenceSorterInterface $sequenceSorter
     * @param FixtureFactory $fixtureFactory
     * @param array $config [optional]
     */
    public function __construct(
        SimpleElement $element,
        BlockFactory $blockFactory,
        Mapper $mapper,
        BrowserInterface $browser,
        SequenceSorterInterface $sequenceSorter,
        FixtureFactory $fixtureFactory,
        array $config = []
    ) {
        parent::__construct($element, $blockFactory, $mapper, $browser, $sequenceSorter, $config);
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Fill gift message form on order level
     *
     * @param GiftMessage $giftMessage
     * @return void
     */
    public function fillGiftMessageOrder(GiftMessage $giftMessage)
    {
        /** @var \Magento\GiftMessage\Test\Block\Cart\GiftOptions\GiftMessageForm $giftMessageForm */
        if ($giftMessage->getAllowGiftMessagesForOrder() === 'Yes') {
            $this->_rootElement->find($this->allowGiftOptions)->click();
            $giftMessageForm = $this->blockFactory->create(
                'Magento\GiftMessage\Test\Block\Cart\GiftOptions\GiftMessageForm',
                ['element' => $this->_rootElement->find($this->giftMessageOrderForm)]
            );
            $formData = [
                'sender' => $giftMessage->getSender(),
                'recipient' => $giftMessage->getRecipient(),
                'message' => $giftMessage->getMessage()
            ];
            $formData = $this->fixtureFactory->createByCode('giftMessage', ['data' => $formData]);
            $giftMessageForm->fill($formData);
            $this->_rootElement->find($this->giftMessageOrderButton)->click();
            $this->waitForElementVisible($this->giftMessageSummary);
        }
    }
}
