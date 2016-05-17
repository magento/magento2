<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Downloadable\Api\Data\SampleInterfaceFactory as SampleFactory;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory as LinkFactory;
use Magento\Framework\App\ObjectManager;
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
    private $sampleFactory;

    /**
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * @var \Magento\Downloadable\Model\Sample\Builder
     */
    private $sampleBuilder;

    /**
     * @var \Magento\Downloadable\Model\Link\Builder
     */
    private $linkBuilder;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
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
                    if (!$linkData || (isset($linkData['is_delete']) && $linkData['is_delete'])) {
                        continue;
                    } else {
                        $links[] = $this->getLinkBuilder()->setData(
                            $linkData
                        )->build(
                            $this->getLinkFactory()->create()
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
                        $samples[] = $this->getSampleBuilder()->setData(
                            $sampleData
                        )->build(
                            $this->getSampleFactory()->create()
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

    /**
     * Get LinkBuilder instance
     *
     * @deprecated
     * @return \Magento\Downloadable\Model\Link\Builder
     */
    private function getLinkBuilder()
    {
        if (!$this->linkBuilder) {
            $this->linkBuilder = ObjectManager::getInstance()->get(\Magento\Downloadable\Model\Link\Builder::class);
        }

        return $this->linkBuilder;
    }

    /**
     * Get SampleBuilder instance
     *
     * @deprecated
     * @return \Magento\Downloadable\Model\Sample\Builder
     */
    private function getSampleBuilder()
    {
        if (!$this->sampleBuilder) {
            $this->sampleBuilder = ObjectManager::getInstance()->get(
                \Magento\Downloadable\Model\Sample\Builder::class
            );
        }

        return $this->sampleBuilder;
    }

    /**
     * Get LinkFactory instance
     *
     * @deprecated
     * @return LinkFactory
     */
    private function getLinkFactory()
    {
        if (!$this->linkFactory) {
            $this->linkFactory = ObjectManager::getInstance()->get(LinkFactory::class);
        }

        return $this->linkFactory;
    }

    /**
     * Get Sample Factory
     *
     * @deprecated
     * @return SampleFactory
     */
    private function getSampleFactory()
    {
        if (!$this->sampleFactory) {
            $this->sampleFactory = ObjectManager::getInstance()->get(SampleFactory::class);
        }

        return $this->sampleFactory;
    }
}
