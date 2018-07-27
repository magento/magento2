<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Downloadable\Api\Data\LinkInterfaceFactory as LinkFactory;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory as SampleFactory;
use Magento\Downloadable\Model\Link\Builder as LinkBuilder;
use Magento\Downloadable\Model\Sample\Builder as SampleBuilder;
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
     * @var SampleBuilder
     */
    private $sampleBuilder;

    /**
     * @var LinkBuilder
     */
    private $linkBuilder;

    /**
     * @param RequestInterface $request
     * @param SampleFactory|null $sampleFactory
     * @param SampleBuilder|null $sampleBuilder
     * @param LinkFactory|null $linkFactory
     * @param LinkBuilder|null $linkBuilder
     * @throws \RuntimeException
     */
    public function __construct(
        RequestInterface $request,
        SampleFactory $sampleFactory = null,
        SampleBuilder $sampleBuilder = null,
        LinkFactory $linkFactory = null,
        LinkBuilder $linkBuilder = null
    ) {
        $this->request = $request;
        $this->sampleFactory = $sampleFactory ?: ObjectManager::getInstance()->get(SampleFactory::class);
        $this->sampleBuilder = $sampleBuilder ?: ObjectManager::getInstance()->get(SampleBuilder::class);
        $this->linkFactory = $linkFactory ?: ObjectManager::getInstance()->get(LinkFactory::class);
        $this->linkBuilder = $linkBuilder ?: ObjectManager::getInstance()->get(LinkBuilder::class);
    }

    /**
     * Prepare product to save
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Magento\Catalog\Model\Product
     * @throws \Magento\Framework\Exception\LocalizedException
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
                        $links[] = $this->linkBuilder
                            ->setData($linkData)
                            ->build($this->linkFactory->create());
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
                        $samples[] = $this->sampleBuilder
                            ->setData($sampleData)
                            ->build($this->sampleFactory->create());
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
