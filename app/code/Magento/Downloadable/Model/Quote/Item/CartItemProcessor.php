<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
                    'qty' => $cartItem->getQty(),
                ]);
            }
        }
        throw new \Exception('Cart item does not contain downloadable links.');
    }

    /**
     * Process cart item product options
     *
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     */
    public function processProductOptions(CartItemInterface $cartItem)
    {
        $options = $cartItem->getOptions();
        $downloadableLinkIds = [];
        if (is_array($options)) {
            /** @var \Magento\Quote\Model\Quote\Item\Option $option */
            foreach ($options as $option) {
                if ($option->getCode() == 'downloadable_link_ids') {
                    $downloadableLinkIds = explode(',', $option->getValue());
                    break;
                }
            }
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
