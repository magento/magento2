<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Catalog\Model\ProductOptionFactory;
use Magento\Catalog\Model\ProductOptionProcessorInterface;
use Magento\Downloadable\Model\DownloadableOptionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

class ProductOptionProcessor implements ProductOptionProcessorInterface
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
    public function convertToBuyRequest(ProductOptionInterface $productOption)
    {
        /** @var DataObject $request */
        $request = $this->objectFactory->create();

        $links = $this->getDownloadableLinks($productOption);
        if (!empty($links)) {
            $request->addData(['links' => $links]);
        }

        return $request;
    }

    /**
     * Retrieve downloadable links option
     *
     * @param ProductOptionInterface $productOption
     * @return array
     */
    protected function getDownloadableLinks(ProductOptionInterface $productOption)
    {
        if ($productOption
            && $productOption->getExtensionAttributes()
            && $productOption->getExtensionAttributes()->getDownloadableOption()
        ) {
            return $productOption->getExtensionAttributes()
                ->getDownloadableOption()
                ->getDownloadableLinks();
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToProductOption(DataObject $request)
    {
        /** @var DownloadableOption $downloadableOption */
        $downloadableOption = $this->downloadableOptionFactory->create();

        $links = $request->getLinks();
        if (!empty($links) && is_array($links)) {
            $this->dataObjectHelper->populateWithArray(
                $downloadableOption,
                ['downloadable_links' => $links],
                'Magento\Downloadable\Api\Data\DownloadableOptionInterface'
            );

            return ['downloadable_option' => $downloadableOption];
        }

        return [];
    }
}
