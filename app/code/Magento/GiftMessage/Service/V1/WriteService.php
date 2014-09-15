<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GiftMessage\Service\V1;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\NoSuchEntityException;

class WriteService implements WriteServiceInterface
{
    /**
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\GiftMessage\Model\GiftMessageManager
     */
    protected $giftMessageManager;

    /**
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Service\V1\Product\ProductLoader
     */
    protected $productLoader;

    /**
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\GiftMessage\Model\GiftMessageManager $giftMessageManager
     * @param \Magento\GiftMessage\Helper\Message $helper
     * @param \Magento\Catalog\Service\V1\Product\ProductLoader $productLoader
     */
    public function __construct(
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\GiftMessage\Model\GiftMessageManager $giftMessageManager,
        \Magento\GiftMessage\Helper\Message $helper,
        \Magento\Catalog\Service\V1\Product\ProductLoader $productLoader
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->giftMessageManager = $giftMessageManager;
        $this->storeManager = $storeManager;
        $this->productLoader = $productLoader;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function setForQuote($cartId, \Magento\GiftMessage\Service\V1\Data\Message $giftMessage)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);

        if (0 == $quote->getItemsCount()) {
            throw new InputException('Gift Messages is not applicable for empty cart');
        }

        if ($quote->isVirtual()) {
            throw new InvalidTransitionException('Gift Messages is not applicable for virtual products');
        }

        $this->setMessage($quote, 'quote', $giftMessage);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setForItem($cartId, \Magento\GiftMessage\Service\V1\Data\Message $giftMessage, $itemId)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);

        if (!$item = $quote->getItemById($itemId)) {
            throw new NoSuchEntityException("There is no product with provided  itemId: $itemId in the cart");
        };

        if ($item->getIsVirtual()) {
            throw new InvalidTransitionException('Gift Messages is not applicable for virtual products');
        }

        $this->setMessage($quote, 'quote_item', $giftMessage, $itemId);
        return true;
    }

    /**
     * Set gift message to item or quote
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @param string $type
     * @param \Magento\GiftMessage\Service\V1\Data\Message $giftMessage
     * @param null|int $entityId
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     */
    protected function setMessage(\Magento\Sales\Model\Quote $quote, $type, $giftMessage, $entityId = null)
    {
        if (is_null($quote->getBillingAddress()->getCountryId())) {
            throw new InvalidTransitionException('Billing address is not set');
        }

        // check if shipping address is set
        if (is_null($quote->getShippingAddress()->getCountryId())) {
            throw new InvalidTransitionException('Shipping address is not set');
        }

        $configType = $type == 'quote' ? '' : 'items';
        if (!$this->helper->getIsMessagesAvailable($configType, $quote, $this->storeManager->getStore())) {
            throw new CouldNotSaveException('Gift Message is not available');
        }
        $message[$type][$entityId] = [
            'from' => $giftMessage->getSender(),
            'to' => $giftMessage->getRecipient(),
            'message' => $giftMessage->getMessage()
        ];

        try {
            $this->giftMessageManager->add($message, $quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not add gift message to shopping cart');
        }
    }
}
