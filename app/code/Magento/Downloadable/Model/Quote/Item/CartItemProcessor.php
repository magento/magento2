<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

/**
 * Class \Magento\Downloadable\Model\Quote\Item\CartItemProcessor
 *
 * @since 2.0.0
 */
class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var DataObjectFactory
     * @since 2.0.0
     */
    private $objectFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * @since 2.0.0
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Downloadable\Model\DownloadableOptionFactory
     * @since 2.0.0
     */
    private $downloadableOptionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ProductOptionFactory
     * @since 2.0.0
     */
    private $productOptionFactory;

    /**
     * @var \Magento\Quote\Api\Data\ProductOptionExtensionFactory
     * @since 2.0.0
     */
    private $extensionFactory;

    /**
     * @param DataObjectFactory $objectFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Downloadable\Model\DownloadableOptionFactory $downloadableOptionFactory
     * @param \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory
     * @param \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        if ($cartItem->getProductOption()
            && $cartItem->getProductOption()->getExtensionAttributes()->getDownloadableOption()
        ) {
            $downloadableLinks = $cartItem->getProductOption()->getExtensionAttributes()->getDownloadableOption()
                ->getDownloadableLinks();
            if (!empty($downloadableLinks)) {
                return $this->objectFactory->create(
                    ['links' => $downloadableLinks]
                );
            }
        }
        return null;
    }

    /**
     * Process cart item product options
     *
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     * @since 2.0.0
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
            \Magento\Downloadable\Api\Data\DownloadableOptionInterface::class
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
