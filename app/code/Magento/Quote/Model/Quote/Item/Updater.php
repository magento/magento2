<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Quote\Model\Quote\Item;
use Laminas\Code\Exception\InvalidArgumentException;

/**
 * Quote item updater
 */
class Updater
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param ProductFactory $productFactory
     * @param FormatInterface $localeFormat
     * @param ObjectFactory $objectFactory
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        ProductFactory $productFactory,
        FormatInterface $localeFormat,
        ObjectFactory $objectFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->productFactory = $productFactory;
        $this->localeFormat = $localeFormat;
        $this->objectFactory = $objectFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Update quote item qty.
     *
     * Custom price is updated in case 'custom_price' value exists
     *
     * @param Item $item
     * @param array $info
     * @throws InvalidArgumentException
     * @return Updater
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function update(Item $item, array $info)
    {
        if (!isset($info['qty'])) {
            throw new InvalidArgumentException(__('The qty value is required to update quote item.'));
        }
        $itemQty = $info['qty'];
        if ($item->getProduct()->getStockItem()) {
            if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                $itemQty = (int)$info['qty'];
            } else {
                $item->setIsQtyDecimal(1);
            }
        }
        $itemQty = $itemQty > 0 ? $itemQty : 1;
        if (isset($info['custom_price'])) {
            $this->setCustomPrice($info, $item);
        } elseif ($item->hasData('custom_price')) {
            $this->unsetCustomPrice($item);
        }

        if (empty($info['action']) || !empty($info['configured'])) {
            $noDiscount = !isset($info['use_discount']);
            $item->setQty($itemQty);
            $item->setNoDiscount($noDiscount);
            $item->getProduct()->setIsSuperMode(true);
            $item->getProduct()->unsSkipCheckRequiredOption();
            $item->checkData();
        }

        return $this;
    }

    /**
     * Prepares custom price and sets into a BuyRequest object as option of quote item
     *
     * @param array $info
     * @param Item $item
     * @return void
     */
    protected function setCustomPrice(array $info, Item $item)
    {
        $itemPrice = $this->parseCustomPrice($info['custom_price']);
        /** @var \Magento\Framework\DataObject $infoBuyRequest */
        $infoBuyRequest = $item->getBuyRequest();
        if ($infoBuyRequest) {
            $infoBuyRequest->setCustomPrice($itemPrice);

            $infoBuyRequest->setValue($this->serializer->serialize($infoBuyRequest->getData()));
            $infoBuyRequest->setCode('info_buyRequest');
            $infoBuyRequest->setProduct($item->getProduct());

            $item->addOption($infoBuyRequest);
        }

        $item->setCustomPrice($itemPrice);
        $item->setOriginalCustomPrice($itemPrice);
    }

    /**
     * Unset custom_price data for quote item
     *
     * @param Item $item
     * @return void
     */
    protected function unsetCustomPrice(Item $item)
    {
        /** @var \Magento\Framework\DataObject $infoBuyRequest */
        $infoBuyRequest = $item->getBuyRequest();
        if ($infoBuyRequest->hasData('custom_price')) {
            $infoBuyRequest->unsetData('custom_price');

            $infoBuyRequest->setValue($this->serializer->serialize($infoBuyRequest->getData()));
            $infoBuyRequest->setCode('info_buyRequest');
            $infoBuyRequest->setProduct($item->getProduct());
            $item->addOption($infoBuyRequest);
        }

        $item->setData('custom_price', null);
        $item->setData('original_custom_price', null);
    }

    /**
     * Return formatted price
     *
     * @param float|int $price
     * @return float|int
     */
    protected function parseCustomPrice($price)
    {
        $price = $this->localeFormat->getNumber($price);
        return $price > 0 ? $price : 0;
    }
}
