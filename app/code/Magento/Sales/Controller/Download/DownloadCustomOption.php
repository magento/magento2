<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Download;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Order\Item;

/**
 * Download a product custom option file.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadCustomOption extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Sales\Model\Download
     */
    protected $download;

    /**
     * @var \Magento\Framework\Unserialize\Unserialize
     * @deprecated 101.0.0
     * @deprecated No longer used
     * @see $serializer
     */
    protected $unserialize;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\Sales\Model\Download $download
     * @param \Magento\Framework\Unserialize\Unserialize $unserialize
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param CollectionFactory|null $itemCollectionFactory
     * @param OrderCollectionFactory|null $orderCollectionFactory
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        \Magento\Sales\Model\Download $download,
        \Magento\Framework\Unserialize\Unserialize $unserialize,
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ?CollectionFactory $itemCollectionFactory = null,
        ?OrderCollectionFactory $orderCollectionFactory = null
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->download = $download;
        $this->unserialize = $unserialize;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\Serializer\Json::class
        );
        $this->itemCollectionFactory = $itemCollectionFactory ?: ObjectManager::getInstance()->get(
            CollectionFactory::class
        );
        $this->orderCollectionFactory = $orderCollectionFactory ?: ObjectManager::getInstance()->get(
            OrderCollectionFactory::class
        );
    }

    /**
     * Custom options download action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        $quoteItemOptionId = $this->getRequest()->getParam('id');
        /** @var $option \Magento\Quote\Model\Quote\Item\Option */
        $option = $this->_objectManager->create(
            \Magento\Quote\Model\Quote\Item\Option::class
        )->load($quoteItemOptionId);
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();

        try {
            $optionData = $option->getId()
                ? $this->getQuoteOptionValue($option)
                : $this->getOrderOptionValue();
            if (!is_string($optionData) || $optionData === '') {
                return $resultForward->forward('noroute');
            }
            $info = $this->serializer->unserialize($optionData);
            if (!$this->isKeyValid($info)) {
                return $resultForward->forward('noroute');
            }
            return $this->download->createResponse($info);
        } catch (\Exception $e) {
            return $resultForward->forward('noroute');
        }
    }

    /**
     * Get the file metadata from a live quote option.
     *
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @return string|null
     */
    private function getQuoteOptionValue(\Magento\Quote\Model\Quote\Item\Option $option): ?string
    {
        $code = (string)$option->getCode();
        if (strpos($code, AbstractType::OPTION_PREFIX) !== 0) {
            return null;
        }
        $optionId = substr($code, strlen(AbstractType::OPTION_PREFIX));
        if (!ctype_digit($optionId)) {
            return null;
        }
        $productOption = $this->_objectManager->create(\Magento\Catalog\Model\Product\Option::class);
        $productOption->load($optionId);
        if ($productOption->getId() && $productOption->getType() != 'file') {
            return null;
        }
        return $option->getValue();
    }

    /**
     * Get persisted file metadata after the quote has been removed.
     *
     * @return string|null
     */
    private function getOrderOptionValue(): ?string
    {
        $quoteId = $this->getRequest()->getParam('quote');
        $quoteItemId = $this->getRequest()->getParam('item');
        $optionId = $this->getRequest()->getParam('option');
        if (!$this->isIdentifier($quoteId)
            || !$this->isIdentifier($quoteItemId)
            || !$this->isIdentifier($optionId)
        ) {
            return null;
        }
        $orderIds = $this->orderCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quoteId)
            ->getAllIds();
        if (!$orderIds) {
            return null;
        }
        $items = $this->itemCollectionFactory->create()
            ->addFieldToFilter('order_id', ['in' => $orderIds])
            ->addFieldToFilter('quote_item_id', $quoteItemId);
        foreach ($items as $item) {
            $value = $this->findOrderOptionValue($item, (string)$optionId);
            if ($value !== null) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Find matching file metadata with a valid download key.
     *
     * @param Item $item
     * @param string $optionId
     * @return string|null
     */
    private function findOrderOptionValue(Item $item, string $optionId): ?string
    {
        try {
            $options = $item->getProductOptionByCode('options');
            if (!is_array($options)) {
                return null;
            }
            foreach ($options as $option) {
                if ($this->isFileOption($option, $optionId)
                    && $this->isKeyValid($this->serializer->unserialize($option['option_value']))
                ) {
                    return $option['option_value'];
                }
            }
        } catch (\InvalidArgumentException $e) {
            return null;
        }
        return null;
    }

    /**
     * Check the persisted custom option before reading its value.
     *
     * @param mixed $option
     * @param string $optionId
     * @return bool
     */
    private function isFileOption(mixed $option, string $optionId): bool
    {
        if (!is_array($option)) {
            return false;
        }
        return $this->isIdentifier($option['option_id'] ?? null)
            && (string)$option['option_id'] === $optionId
            && ($option['option_type'] ?? '') === 'file'
            && is_string($option['option_value'] ?? null);
    }

    /**
     * Check a download identifier supplied by the request.
     *
     * @param mixed $value
     * @return bool
     */
    private function isIdentifier(mixed $value): bool
    {
        return (is_string($value) || is_int($value)) && ctype_digit((string)$value);
    }

    /**
     * Validate the capability key before creating a file response.
     *
     * @param mixed $info
     * @return bool
     */
    private function isKeyValid(mixed $info): bool
    {
        $key = $this->getRequest()->getParam('key');
        return is_array($info)
            && is_string($info['secret_key'] ?? null)
            && $info['secret_key'] !== ''
            && is_string($key)
            && hash_equals($info['secret_key'], $key);
    }

    /**
     * Ends execution process
     *
     * @return void
     */
    protected function endExecute()
    {
        // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
        exit(0);
    }
}
