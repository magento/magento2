<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Helper;

use Magento\Catalog\Model\Product\Attribute\Source\Boolean;

/**
 * Gift Message helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Message extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Gift messages allow section in configuration
     *
     */
    const XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS = 'sales/gift_options/allow_items';

    const XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER = 'sales/gift_options/allow_order';

    /**
     * Next id for edit gift message block
     *
     * @var int
     */
    protected $_nextId = 0;

    /**
     * Inner cache
     *
     * @var array
     */
    protected $_innerCache = [];

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * @var \Magento\GiftMessage\Model\MessageFactory
     */
    protected $_giftMessageFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Pages to skip message checks
     *
     * @var array
     */
    protected $skipMessageCheck = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\GiftMessage\Model\MessageFactory $giftMessageFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param array $skipMessageCheck
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\GiftMessage\Model\MessageFactory $giftMessageFactory,
        \Magento\Framework\Escaper $escaper,
        $skipMessageCheck = []
    ) {
        $this->_escaper = $escaper;
        $this->productRepository = $productRepository;
        $this->_layoutFactory = $layoutFactory;
        $this->_giftMessageFactory = $giftMessageFactory;
        $this->skipMessageCheck = $skipMessageCheck;
        $this->_storeManager = $storeManager;

        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve inline giftmessage edit form for specified entity
     *
     * @param string $type
     * @param \Magento\Framework\DataObject $entity
     * @param bool $dontDisplayContainer
     * @return string
     */
    public function getInline($type, \Magento\Framework\DataObject $entity, $dontDisplayContainer = false)
    {
        if (!$this->skipPage($type) && !$this->isMessagesAllowed($type, $entity)) {
            return '';
        }
        return $this->_layoutFactory->create()->createBlock(\Magento\GiftMessage\Block\Message\Inline::class)
            ->setId('giftmessage_form_' . $this->_nextId++)
            ->setDontDisplayContainer($dontDisplayContainer)
            ->setEntity($entity)
            ->setCheckoutType($type)->toHtml();
    }

    /**
     * Skip page by page type
     *
     * @param string $pageType
     * @return bool
     */
    protected function skipPage($pageType)
    {
        return in_array($pageType, $this->skipMessageCheck);
    }

    /**
     * Check if giftmessages is allowed for specified entity.
     *
     * @param string $type
     * @param \Magento\Framework\DataObject $entity
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool|string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isMessagesAllowed($type, \Magento\Framework\DataObject $entity, $store = null)
    {
        if ($type == 'items') {
            $items = $entity->getAllItems();
            if (!is_array($items) || empty($items)) {
                return $this->scopeConfig->getValue(
                    self::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                );
            }
            if ($entity instanceof \Magento\Quote\Model\Quote) {
                $_type = $entity->getIsMultiShipping() ? 'address_item' : 'item';
            } else {
                $_type = 'order_item';
            }
            foreach ($items as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($this->isMessagesAllowed($_type, $item, $store)) {
                    return true;
                }
            }
        } elseif ($type == 'item') {
            return $this->_getDependenceFromStoreConfig($entity->getProduct()->getGiftMessageAvailable(), $store);
        } elseif ($type == 'order_item') {
            return $this->_getDependenceFromStoreConfig($entity->getGiftMessageAvailable(), $store);
        } elseif ($type == 'address_item') {
            $storeId = is_numeric($store) ? $store : $this->_storeManager->getStore($store)->getId();
            if (!$this->isCached('address_item_' . $entity->getProductId())) {
                try {
                    $giftMessageAvailable = $this->productRepository->getById($entity->getProductId(), false, $storeId)
                        ->getGiftMessageAvailable();
                } catch (\Magento\Framework\Exception\NoSuchEntityException $noEntityException) {
                    $giftMessageAvailable = null;
                }
                $this->setCached('address_item_' . $entity->getProductId(), $giftMessageAvailable);
            }
            return $this->_getDependenceFromStoreConfig(
                $this->getCached('address_item_' . $entity->getProductId()),
                $store
            );
        } else {
            return $this->scopeConfig->getValue(
                self::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        return false;
    }

    /**
     * Check availability of gift messages from store config if flag eq 2.
     *
     * @param bool $productConfig
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool|string|null
     */
    protected function _getDependenceFromStoreConfig($productConfig, $store = null)
    {
        $result = $this->scopeConfig->getValue(
            self::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($productConfig === null || '' === $productConfig || $productConfig == Boolean::VALUE_USE_CONFIG) {
            return $result;
        } else {
            return $productConfig;
        }
    }

    /**
     * Retrieve escaped and preformated gift message text for specified entity
     *
     * @param \Magento\Framework\DataObject $entity
     * @return string|null
     */
    public function getEscapedGiftMessage(\Magento\Framework\DataObject $entity)
    {
        $message = $this->getGiftMessageForEntity($entity);
        if ($message) {
            return nl2br($this->_escaper->escapeHtml($message->getMessage()));
        }
        return null;
    }

    /**
     * Retrieve gift message for entity. If message not exists return null
     *
     * @param \Magento\Framework\DataObject $entity
     * @return \Magento\GiftMessage\Model\Message
     */
    public function getGiftMessageForEntity(\Magento\Framework\DataObject $entity)
    {
        if ($entity->getGiftMessageId() && !$entity->getGiftMessage()) {
            $message = $this->getGiftMessage($entity->getGiftMessageId());
            $entity->setGiftMessage($message);
        }
        return $entity->getGiftMessage();
    }

    /**
     * Retrieve internal cached data with specified key.
     *
     * If cached data not found return null.
     *
     * @param string $key
     * @return mixed
     */
    public function getCached($key)
    {
        if ($this->isCached($key)) {
            return $this->_innerCache[$key];
        }
        return null;
    }

    /**
     * Check availability for internal cached data with specified key
     *
     * @param string $key
     * @return bool
     */
    public function isCached($key)
    {
        return isset($this->_innerCache[$key]);
    }

    /**
     * Set internal cache data with specified key
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setCached($key, $value)
    {
        $this->_innerCache[$key] = $value;
        return $this;
    }

    /**
     * Check availability for onepage checkout items
     *
     * @param array $quote
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAvailableForQuoteItems($quote, $store = null)
    {
        foreach ($quote->getAllItems() as $item) {
            if ($this->isMessagesAllowed('item', $item, $store)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check availability for multishipping checkout items
     *
     * @param array $items
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAvailableForAddressItems($items, $store = null)
    {
        foreach ($items as $item) {
            if ($this->isMessagesAllowed('address_item', $item, $store)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve gift message with specified id
     *
     * @param int $messageId
     * @return \Magento\GiftMessage\Model\Message
     */
    public function getGiftMessage($messageId = null)
    {
        $message = $this->_giftMessageFactory->create();
        if ($messageId !== null) {
            $message->load($messageId);
        }
        return $message;
    }
}
