<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Downloadable\Api\Data\SampleInterfaceFactory as SampleFactory;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory as LinkFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class Downloadable
 */
class Downloadable
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SampleFactory
     */
    protected $sampleFactory;

    /**
     * @var LinkFactory
     */
    protected $linkFactory;

    /**
     * @param RequestInterface $request
     * @param SampleFactory $sampleFactory
     * @param LinkFactory $linkFactory
     */
    public function __construct(
        RequestInterface $request,
        SampleFactory $sampleFactory,
        LinkFactory $linkFactory
    ) {
        $this->request = $request;
        $this->linkFactory = $linkFactory;
        $this->sampleFactory = $sampleFactory;
    }

    /**
     * Prepare product to save
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($downloadable = $this->request->getPost('downloadable')) {
            $product->setDownloadableData($downloadable);
            $extension = $product->getExtensionAttributes();
            if (isset($downloadable['link']) && is_array($downloadable['link'])) {
                $links = [];
                foreach ($downloadable['link'] as $linkData) {
                    if (!$linkData || (isset($linkData['is_delete']) && (bool)$linkData['is_delete'])) {
                        continue;
                    } else {
                        unset($linkData['link_id']);
                        // TODO: need to implement setLinkFileContent()
                        $link = $this->linkFactory->create(['data' => $linkData]);
                        if (isset($linkData['type'])) {
                            $link->setLinkType($linkData['type']);
                        }
                        if (isset($linkData['file'])) {
                            $link->setLinkFile($linkData['file']);
                        }
                        if (isset($linkData['file_content'])) {
                            $link->setLinkFileContent($linkData['file_content']);
                        }
                        $link->setId(null);
                        if (isset($linkData['sample']['type'])) {
                            $link->setSampleType($linkData['sample']['type']);
                        }
                        if (isset($linkData['sample']['file'])) {
                            $link->setSampleFile($linkData['sample']['file']);
                        }
                        if (isset($linkData['sample']['url'])) {
                            $link->setSampleUrl($linkData['sample']['url']);
                        }
                        if (isset($linkData['sample']['file_content'])) {
                            $link->setSampleFileContent($linkData['file_content']);
                        }
                        $link->setStoreId($product->getStoreId());
                        $link->setWebsiteId($product->getStore()->getWebsiteId());
                        $link->setProductWebsiteIds($product->getWebsiteIds());
                        if (!$link->getSortOrder()) {
                            $link->setSortOrder(1);
                        }
                        if (null === $link->getPrice()) {
                            $link->setPrice(0);
                        }
                        if ($link->getIsUnlimited()) {
                            $link->setNumberOfDownloads(0);
                        }
                        $links[] = $link;
                    }
                }
                $extension->setDownloadableProductLinks($links);
            }
            if (isset($downloadable['sample']) && is_array($downloadable['sample'])) {
                $samples = [];
                foreach ($downloadable['sample'] as $sampleData) {
                    if (!$sampleData || (isset($sampleData['is_delete']) && (bool)$sampleData['is_delete'])) {
                        continue;
                    } else {
                        unset($sampleData['sample_id']);
                        $sample = $this->sampleFactory->create(['data' => $sampleData]);
                        $sample->setId(null);
                        $sample->setStoreId($product->getStoreId());
                        if (isset($sampleData['type'])) {
                            $sample->setSampleType($sampleData['type']);
                        }
                        if (isset($sampleData['sample_url'])) {
                            $sample->setSampleUrl($sampleData['sample_url']);
                        }
                        if (!$sample->getSortOrder()) {
                            $sample->setSortOrder(1);
                        }
                        $samples[] = $sample;
                    }
                }
                $extension->setDownloadableProductSamples($samples);
            }
            $product->setExtensionAttributes($extension);
            if ($product->getLinksPurchasedSeparately()) {
                $product->setTypeHasRequiredOptions(true)->setRequiredOptions(true);
            } else {
                $product->setTypeHasRequiredOptions(false)->setRequiredOptions(false);
            }
        }
        return $product;
    }
}
