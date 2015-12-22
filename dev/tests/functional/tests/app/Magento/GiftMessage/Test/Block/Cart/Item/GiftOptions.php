<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Cart\Item;

use Magento\Mtf\Block\BlockFactory;
use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Block\Mapper;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Add gift options on checkout cart item level
 */
class GiftOptions extends Form
{
    /**
     * Selector for gift message on item form
     *
     * @var string
     */
    protected $giftMessageItemForm = '.gift-message fieldset';

    /**
     * Allow Gift Options for items
     *
     * @var string
     */
    protected $allowGiftOptions = '//a[contains(@class,"action-gift")][ancestor::tbody[contains(.,"%s")]]';

    /**
     * Selector for apply Gift Message button on order
     *
     * @var string
     */
    protected $giftMessageItemButton = ".action-update";

    /**
     * Selector for Gift Message Summary
     *
     * @var string
     */
    protected $giftMessageSummary = '//div[@class="gift-message-summary"][ancestor::tbody[contains(.,"%s")]]';

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
     * @param FixtureFactory $fixtureFactory
     * @param array $config [optional]
     */
    public function __construct(
        SimpleElement $element,
        BlockFactory $blockFactory,
        Mapper $mapper,
        BrowserInterface $browser,
        FixtureFactory $fixtureFactory,
        array $config = []
    ) {
        $this->fixtureFactory = $fixtureFactory;
        parent::__construct($element, $blockFactory, $mapper, $browser, $config);
    }

    /**
     * Fill gift message form on item level
     *
     * @param GiftMessage $giftMessage
     * @param array $products
     * @return void
     */
    public function fillGiftMessageItem(GiftMessage $giftMessage, $products = [])
    {
        /** @var \Magento\GiftMessage\Test\Block\Cart\GiftOptions\GiftMessageForm $giftMessageForm */
        if ($giftMessage->getAllowGiftOptionsForItems() === 'Yes') {
            foreach ($products as $product) {
                if ($product->getProductHasWeight() == 'Yes') {
                    $this->_rootElement->find(
                        sprintf($this->allowGiftOptions, $product->getName()),
                        Locator::SELECTOR_XPATH
                    )->click();
                    $giftMessageForm = $this->blockFactory->create(
                        'Magento\GiftMessage\Test\Block\Cart\GiftOptions\GiftMessageForm',
                        ['element' => $this->_rootElement->find($this->giftMessageItemForm)]
                    );
                    $giftMessage = $giftMessage->getItems()[0];
                    $formData = [
                        'sender' => $giftMessage->getSender(),
                        'recipient' => $giftMessage->getRecipient(),
                        'message' => $giftMessage->getMessage()
                    ];
                    $formData = $this->fixtureFactory->createByCode('giftMessage', ['data' => $formData]);
                    $giftMessageForm->fill($formData);
                    $this->_rootElement->find($this->giftMessageItemButton)->click();
                    $this->waitForElementVisible(
                        sprintf($this->giftMessageSummary, $product->getName()),
                        Locator::SELECTOR_XPATH
                    );
                }
            }
        }
    }
}
