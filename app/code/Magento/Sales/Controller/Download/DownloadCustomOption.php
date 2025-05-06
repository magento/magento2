<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Download;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Download Custom Option Controller
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
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\Sales\Model\Download $download
     * @param \Magento\Framework\Unserialize\Unserialize $unserialize
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param CollectionFactory $itemCollectionFactory
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        \Magento\Sales\Model\Download $download,
        \Magento\Framework\Unserialize\Unserialize $unserialize,
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ?CollectionFactory $itemCollectionFactory = null
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
    }

    /**
     * Custom options download action
     *
     * @return void|\Magento\Framework\Controller\Result\Forward
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $quoteItemOptionId = $this->getRequest()->getParam('id');
        $quoteItemId = $this->getRequest()->getParam('item');
        $optionId = $this->getRequest()->getParam('option');
        /** @var $option \Magento\Quote\Model\Quote\Item\Option */
        $option = $this->_objectManager->create(
            \Magento\Quote\Model\Quote\Item\Option::class
        )->load($quoteItemOptionId);
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();

        if (!$option->getId()) {
            $optionData = $this->getOptionFromSalesItem($quoteItemId, $optionId);
        } else {
            $optionData = $this->getOptionFromQuoteItem($option);
        }

        if ($optionData) {
            try {
                $info = $this->serializer->unserialize($optionData);
                if ($this->getRequest()->getParam('key') != $info['secret_key']) {
                    return $resultForward->forward('noroute');
                }
                return $this->download->createResponse($info);
            } catch (\Exception $e) {
                return $resultForward->forward('noroute');
            }
        }
        $this->endExecute();
    }

    /**
     * Get custom option data from quote item option
     *
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @return string|null
     */
    protected function getOptionFromQuoteItem($option)
    {
        $optionId = null;
        if ($option->getCode() && strpos($option->getCode(), AbstractType::OPTION_PREFIX) === 0) {
            $optionId = str_replace(AbstractType::OPTION_PREFIX, '', $option->getCode());
            if ((int)$optionId != $optionId) {
                $optionId = null;
            }
        }
        $productOption = null;
        if ($optionId) {
            /** @var $productOption \Magento\Catalog\Model\Product\Option */
            $productOption = $this->_objectManager->create(
                \Magento\Catalog\Model\Product\Option::class
            );
            $productOption->load($optionId);
        }

        if ($productOption->getId() && $productOption->getType() != 'file') {
            return null;
        }
        return $option->getValue();
    }

    /**
     * Get custom option data from sales item
     *
     * @param string|null $quoteItemId
     * @param string|null $optionId
     * @return string|null
     */
    protected function getOptionFromSalesItem($quoteItemId, $optionId)
    {
        if (!$quoteItemId || !$optionId) {
            return null;
        }
        $collection = $this->itemCollectionFactory->create();
        $quoteItem = $collection->addFieldToFilter(OrderItemInterface::QUOTE_ITEM_ID, $quoteItemId)->getFirstItem();
        if ($quoteItem && $quoteItem->getId()) {
            try {
                $options = $quoteItem->getProductOptionByCode('options');
                $key = array_search($optionId, array_column($options, 'option_id'));
                if ($key !== false && $options[$key]['option_value'] && $options[$key]['option_type'] == 'file') {
                    return $options[$key]['option_value'];
                }
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
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
