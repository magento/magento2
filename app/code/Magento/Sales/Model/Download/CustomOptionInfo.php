<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Download;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Catalog\Model\Product\OptionFactory as ProductOptionFactory;
use Magento\Quote\Model\Quote\Item\OptionFactory as QuoteItemOptionFactory;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Serialize\Serializer\Json;

class CustomOptionInfo
{
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var ProductOptionFactory
     */
    private $productOptionFactory;

    /**
     * @var QuoteItemOptionFactory
     */
    private $quoteItemOptionFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param ProductOptionFactory $productOptionFactory
     * @param QuoteItemOptionFactory $quoteItemOptionFactory
     * @param Json $serializer
     */
    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        ProductOptionFactory $productOptionFactory,
        QuoteItemOptionFactory $quoteItemOptionFactory,
        Json $serializer
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->productOptionFactory = $productOptionFactory;
        $this->quoteItemOptionFactory = $quoteItemOptionFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param int $quoteItemid
     * @param int $orderItemId
     * @param int $optionId
     * @return array|bool|float|int|mixed|string|null
     * @throws LocalizedException|NoSuchEntityException
     */
    public function search($quoteItemid, $orderItemId, $optionId)
    {
        if ($orderItemId && $optionId) {
            return $this->loadOptionByOrderItemId($orderItemId, $optionId);
        }
        return $this->loadOptionByQuoteItemId($quoteItemid);
    }

    /**
     * @param int $id
     * @return array|bool|float|int|mixed|string|null
     * @throws NoSuchEntityException|LocalizedException
     */
    private function loadOptionByQuoteItemId(int $id)
    {
        $option = $this->quoteItemOptionFactory->create();
        $option->load($id);

        if (!$option->getId()) {
            throw new NoSuchEntityException(__("Quote Item Option %1 not found", $id));
        }

        $optionId = null;
        if (strpos($option->getCode(), AbstractType::OPTION_PREFIX) === 0) {
            $optionId = str_replace(AbstractType::OPTION_PREFIX, '', $option->getCode());
            if ((int)$optionId != $optionId) {
                $optionId = null;
            }
        }

        $productOption = null;
        if ($optionId) {
            $productOption = $this->productOptionFactory->create();
            $productOption->load($optionId);
        }

        if (!$productOption->getId()) {
            throw new NoSuchEntityException(__("Product Option %1 not found", $optionId));
        }

        if ($productOption->getType() != 'file') {
            throw new LocalizedException(__("the product option assigned is not type file"));
        }

        return $info = $this->serializer->unserialize($option->getValue());
    }

    /**
     * @param int $orderItemId
     * @param int $optionId
     * @return array|bool
     * @throws LocalizedException
     */
    private function loadOptionByOrderItemId(int $orderItemId, int $optionId)
    {
        $orderItem = $this->orderItemRepository->get($orderItemId);
        $orderItemProductOptions = $orderItem->getProductOptions();
        return $this->loadInfoByOptionData($orderItemProductOptions, $optionId);
    }

    /**
     * @param array $orderItemProductOptions
     * @param string $optionId
     * @return array
     * @throws LocalizedException
     */
    private function loadInfoByOptionData($orderItemProductOptions, $optionId)
    {
        $infoBuyRequest = $this->loadArrayDataByKey($orderItemProductOptions, 'info_buyRequest');
        if (!isset($infoBuyRequest)) {
            throw new LocalizedException(__("Order item has not info_buyRequest value assigned"));
        }

        $options = $this->loadArrayDataByKey($infoBuyRequest, 'options');
        if (!isset($options)) {
            throw new LocalizedException(__("InfoBuyRequest has not options value assigned"));
        }

        $result = $this->loadArrayDataByKey($options, $optionId);
        if (!isset($result)) {
            throw new LocalizedException(__("InfoBuyRequest has not option %1", $optionId));
        }

        return $result;
    }

    /**
     * @param array $data
     * @param string|int $key
     * @return bool|mixed
     */
    private function loadArrayDataByKey(array $data, $key)
    {
        if (!array_key_exists($key, $data)) {
            return false;
        }

        return $data[$key];
    }
}
