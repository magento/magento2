<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Downloadable\Model\DownloadableOptionFactory
     */
    private $downloadableOptionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ProductOptionFactory
     */
    private $productOptionFactory;

    /**
     * @var \Magento\Quote\Api\Data\ProductOptionExtensionFactory
     */
    private $extensionFactory;

    /**
     * @param DataObjectFactory $objectFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Downloadable\Model\DownloadableOptionFactory $downloadableOptionFactory
     * @param \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory
     * @param \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Downloadable\Model\DownloadableOptionFactory $downloadableOptionFactory,
        \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory,
        \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->downloadableOptionFactory = $downloadableOptionFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        if ($cartItem->getProductOption()
            && $cartItem->getProductOption()->getExtensionAttributes()->getDownloadableOption()
        ) {
            $downloadableLinks = $cartItem->getProductOption()->getExtensionAttributes()->getDownloadableOption()
                ->getDownloadableLinks();
            if (!empty($downloadableLinks)) {
                return $this->objectFactory->create([
                    'links' => $downloadableLinks,
                ]);
            }
        }
        return null;
    }

    /**
     * Process cart item product options
     *
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     */
    public function processOptions(CartItemInterface $cartItem)
    {
        $downloadableLinkIds = [];
        $option = $cartItem->getOptionByCode('downloadable_link_ids');
        if (!empty($option)) {
            $downloadableLinkIds = explode(',', $option->getValue());
        }

        $downloadableOption = $this->downloadableOptionFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $downloadableOption,
            [
                'downloadable_links' => $downloadableLinkIds
            ],
            'Magento\Downloadable\Api\Data\DownloadableOptionInterface'
        );

        $productOption = ($cartItem->getProductOption())
            ? $cartItem->getProductOption()
            : $this->productOptionFactory->create();

        $extensibleAttribute =  ($productOption->getExtensionAttributes())
            ? $productOption->getExtensionAttributes()
            : $this->extensionFactory->create();

        $extensibleAttribute->setDownloadableOption($downloadableOption);
        $productOption->setExtensionAttributes($extensibleAttribute);
        $cartItem->setProductOption($productOption);

        return $cartItem;
    }
}
