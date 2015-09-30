<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;
use Magento\Catalog\Model\ProductOptionFactory;
use Magento\Downloadable\Model\DownloadableOption;
use Magento\Downloadable\Model\DownloadableOptionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item\ProcessorInterface;

class ProductOptionProcessor implements ProcessorInterface
{
    /**
     * @var DataObjectFactory
     */
    protected $objectFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var ProductOptionFactory
     */
    protected $productOptionFactory;

    /**
     * @var ProductOptionExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var DownloadableOptionFactory
     */
    protected $downloadableOptionFactory;

    /**
     * @param DataObjectFactory $objectFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ProductOptionFactory $productOptionFactory
     * @param ProductOptionExtensionFactory $extensionFactory
     * @param DownloadableOptionFactory $downloadableOptionFactory
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        DataObjectHelper $dataObjectHelper,
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $extensionFactory,
        DownloadableOptionFactory $downloadableOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->downloadableOptionFactory = $downloadableOptionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToBuyRequest(OrderItemInterface $orderItem)
    {
        /** @var DataObject $request */
        $request = $this->objectFactory->create();

        $links = $this->getDownloadableLinks($orderItem);
        if (!empty($links)) {
            $request->addData(['links' => $links]);
        }

        return $request;
    }

    /**
     * Retrieve downloadable links option
     *
     * @param OrderItemInterface $orderItem
     * @return array
     */
    protected function getDownloadableLinks(OrderItemInterface $orderItem)
    {
        if ($orderItem->getProductOption()
            && $orderItem->getProductOption()->getExtensionAttributes()
            && $orderItem->getProductOption()->getExtensionAttributes()->getDownloadableOption()
        ) {
            return $orderItem->getProductOption()
                ->getExtensionAttributes()
                ->getDownloadableOption()
                ->getDownloadableLinks();
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function processOptions(OrderItemInterface $orderItem)
    {
        /** @var DownloadableOption $downloadableOption */
        $downloadableOption = $this->downloadableOptionFactory->create();
        $links = $orderItem->getBuyRequest()->getLinks();
        if (!empty($links) && is_array($links)) {
            $this->dataObjectHelper->populateWithArray(
                $downloadableOption,
                ['downloadable_links' => $links],
                'Magento\Downloadable\Api\Data\DownloadableOptionInterface'
            );
        }

        $this->setDownloadableOption($orderItem, $downloadableOption);

        return $orderItem;
    }

    /**
     * Set downloadable option
     *
     * @param OrderItemInterface $orderItem
     * @param DownloadableOption $downloadableOption
     * @return $this
     */
    protected function setDownloadableOption(OrderItemInterface $orderItem, DownloadableOption $downloadableOption)
    {
        if (!$orderItem->getProductOption()) {
            $productOption = $this->productOptionFactory->create();
            $orderItem->setProductOption($productOption);
        }

        if (!$orderItem->getProductOption()->getExtensionAttributes()) {
            $extensionAttributes = $this->extensionFactory->create();
            $orderItem->getProductOption()->setExtensionAttributes($extensionAttributes);
        }

        $orderItem->getProductOption()
            ->getExtensionAttributes()
            ->setDownloadableOption($downloadableOption);

        return $this;
    }
}
