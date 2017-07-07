<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Downloadable\Model\Link\Builder as LinkBuilder;
use Magento\Downloadable\Model\Sample\Builder as SampleBuilder;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory;

class Downloadable
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SampleInterfaceFactory
     */
    private $sampleFactory;

    /**
     * @var LinkInterfaceFactory
     */
    private $linkFactory;

    /**
     * @var SampleBuilder
     */
    private $sampleBuilder;

    /**
     * @var LinkBuilder
     */
    private $linkBuilder;

    /**
     * Constructor
     * 
     * @param RequestInterface $request
     * @param LinkBuilder $linkBuilder
     * @param SampleBuilder $sampleBuilder
     * @param SampleInterfaceFactory $sampleFactory
     * @param LinkInterfaceFactory $linkFactory
     */
    public function __construct(
        RequestInterface $request,
        LinkBuilder $linkBuilder,
        SampleBuilder $sampleBuilder,
        SampleInterfaceFactory $sampleFactory,
        LinkInterfaceFactory $linkFactory
    ) {
        $this->request = $request;
        $this->linkBuilder = $linkBuilder;
        $this->sampleBuilder = $sampleBuilder;
        $this->sampleFactory = $sampleFactory;
        $this->linkFactory = $linkFactory;
    }

    /**
     * Prepare product to save
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $product
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
                    if (!$linkData || (isset($linkData['is_delete']) && $linkData['is_delete'])) {
                        continue;
                    } else {
                        $links[] = $this->linkBuilder->setData(
                            $linkData
                        )->build(
                            $this->linkFactory->create()
                        );
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
                        $samples[] = $this->sampleBuilder->setData(
                            $sampleData
                        )->build(
                            $this->sampleFactory->create()
                        );
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
