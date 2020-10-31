<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Downloadable\Api\Data\LinkInterfaceFactory;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Model\Link\Builder as LinkBuilder;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\ResourceModel\Sample\Collection;
use Magento\Downloadable\Model\Sample\Builder as SampleBuilder;
use Magento\Framework\App\RequestInterface;

/**
 * Class for initialization downloadable info from request.
 */
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
            $product->setTypeId(Type::TYPE_DOWNLOADABLE);
            $product->setDownloadableData($downloadable);
            $extension = $product->getExtensionAttributes();
            $productLinks = $product->getTypeInstance()->getLinks($product);
            $productSamples = $product->getTypeInstance()->getSamples($product);
            if (isset($downloadable['link']) && is_array($downloadable['link'])) {
                $links = [];
                foreach ($downloadable['link'] as $linkData) {
                    if (!$linkData || (isset($linkData['is_delete']) && $linkData['is_delete'])) {
                        continue;
                    } else {
                        $linkData = $this->processLink($linkData, $productLinks);
                        $links[] = $this->linkBuilder->setData(
                            $linkData
                        )->build(
                            $this->linkFactory->create()
                        );
                    }
                }
                $extension->setDownloadableProductLinks($links);
            } else {
                $extension->setDownloadableProductLinks([]);
            }
            if (isset($downloadable['sample']) && is_array($downloadable['sample'])) {
                $samples = [];
                foreach ($downloadable['sample'] as $sampleData) {
                    if (!$sampleData || (isset($sampleData['is_delete']) && (bool)$sampleData['is_delete'])) {
                        continue;
                    } else {
                        $sampleData = $this->processSample($sampleData, $productSamples);
                        $samples[] = $this->sampleBuilder->setData(
                            $sampleData
                        )->build(
                            $this->sampleFactory->create()
                        );
                    }
                }
                $extension->setDownloadableProductSamples($samples);
            } else {
                $extension->setDownloadableProductSamples([]);
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
     * Check Links type and status.
     *
     * @param array $linkData
     * @param array $productLinks
     * @return array
     */
    private function processLink(array $linkData, array $productLinks): array
    {
        $linkId = $linkData['link_id'] ?? null;
        if ($linkId && isset($productLinks[$linkId])) {
            $linkData = $this->processFileStatus($linkData, $productLinks[$linkId]->getLinkFile());
            $linkData['sample'] = $this->processFileStatus(
                $linkData['sample'] ?? [],
                $productLinks[$linkId]->getSampleFile()
            );
        } else {
            $linkData = $this->processFileStatus($linkData, null);
            $linkData['sample'] = $this->processFileStatus($linkData['sample'] ?? [], null);
        }

        return $linkData;
    }

    /**
     * Check Sample type and status.
     *
     * @param array $sampleData
     * @param Collection $productSamples
     * @return array
     */
    private function processSample(array $sampleData, Collection $productSamples): array
    {
        $sampleId = $sampleData['sample_id'] ?? null;
        /** @var \Magento\Downloadable\Model\Sample $productSample */
        $productSample = $sampleId ? $productSamples->getItemById($sampleId) : null;
        if ($sampleId && $productSample) {
            $sampleData = $this->processFileStatus($sampleData, $productSample->getSampleFile());
        } else {
            $sampleData = $this->processFileStatus($sampleData, null);
        }

        return $sampleData;
    }

    /**
     * Compare file path from request with DB and set status.
     *
     * @param array $data
     * @param string|null $file
     * @return array
     */
    private function processFileStatus(array $data, ?string $file): array
    {
        if (isset($data['type']) && $data['type'] === Download::LINK_TYPE_FILE && isset($data['file']['0']['file'])) {
            if ($data['file'][0]['file'] !== $file) {
                $data['file'][0]['status'] = 'new';
            } else {
                $data['file'][0]['status'] = 'old';
            }
        }

        return $data;
    }
}
