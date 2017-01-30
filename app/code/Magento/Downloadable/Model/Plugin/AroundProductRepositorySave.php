<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Model\Plugin;

class AroundProductRepositorySave
{
    /**
     * @var \Magento\Downloadable\Api\LinkRepositoryInterface
     */
    protected $linkRepository;

    /**
     * @var \Magento\Downloadable\Api\SampleRepositoryInterface
     */
    protected $sampleRepository;

    /**
     * @param \Magento\Downloadable\Api\LinkRepositoryInterface $linkRepository
     * @param \Magento\Downloadable\Api\SampleRepositoryInterface $sampleRepository
     */
    public function __construct(
        \Magento\Downloadable\Api\LinkRepositoryInterface $linkRepository,
        \Magento\Downloadable\Api\SampleRepositoryInterface $sampleRepository
    ) {
        $this->linkRepository = $linkRepository;
        $this->sampleRepository = $sampleRepository;
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param bool $saveOptions
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function aroundSave(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Catalog\Api\Data\ProductInterface $product,
        $saveOptions = false
    ) {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $result */
        $result = $proceed($product, $saveOptions);

        if ($product->getTypeId() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $result;
        }

        /* @var \Magento\Catalog\Api\Data\ProductExtensionInterface $options */
        $extendedAttributes = $product->getExtensionAttributes();
        if ($extendedAttributes === null) {
            return $result;
        }
        $links = $extendedAttributes->getDownloadableProductLinks();
        $samples = $extendedAttributes->getDownloadableProductSamples();

        if ($links === null && $samples === null) {
            return $result;
        }

        if ($links !== null) {
            $this->saveLinks($result, $links);
        }
        if ($samples !== null) {
            $this->saveSamples($result, $samples);
        }

        return $subject->get($result->getSku(), false, $result->getStoreId(), true);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Downloadable\Api\Data\LinkInterface[] $links
     * @return $this
     */
    protected function saveLinks(\Magento\Catalog\Api\Data\ProductInterface $product, array $links)
    {
        $existingLinkIds = [];
        //get existing links from extension attribute
        $extensionAttributes = $product->getExtensionAttributes();
        if ($extensionAttributes !== null) {
            $existingLinks = $extensionAttributes->getDownloadableProductLinks();
            if ($existingLinks !== null) {
                foreach ($existingLinks as $existingLink) {
                    $existingLinkIds[] = $existingLink->getId();
                }
            }
        }

        $updatedLinkIds = [];
        foreach ($links as $link) {
            $linkId = $link->getId();
            if ($linkId) {
                $updatedLinkIds[] = $linkId;
            }
            $this->linkRepository->save($product->getSku(), $link);
        }
        $linkIdsToDelete = array_diff($existingLinkIds, $updatedLinkIds);

        foreach ($linkIdsToDelete as $linkId) {
            $this->linkRepository->delete($linkId);
        }
        return $this;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Downloadable\Api\Data\SampleInterface[] $samples
     * @return $this
     */
    protected function saveSamples(\Magento\Catalog\Api\Data\ProductInterface $product, array $samples)
    {
        $existingSampleIds = [];
        $extensionAttributes = $product->getExtensionAttributes();
        if ($extensionAttributes !== null) {
            $existingSamples = $extensionAttributes->getDownloadableProductSamples();
            if ($existingSamples !== null) {
                foreach ($existingSamples as $existingSample) {
                    $existingSampleIds[] = $existingSample->getId();
                }
            }
        }

        $updatedSampleIds = [];
        foreach ($samples as $sample) {
            $sampleId = $sample->getId();
            if ($sampleId) {
                $updatedSampleIds[] = $sampleId;
            }
            $this->sampleRepository->save($product->getSku(), $sample);
        }
        $sampleIdsToDelete = array_diff($existingSampleIds, $updatedSampleIds);

        foreach ($sampleIdsToDelete as $sampleId) {
            $this->sampleRepository->delete($sampleId);
        }
        return $this;
    }
}
