<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\SampleRepositoryInterface as SampleRepository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Read Handler for Downloadable Product Samples.
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var SampleRepository
     */
    protected $sampleRepository;

    /**
     * @param SampleRepository $sampleRepository
     */
    public function __construct(SampleRepository $sampleRepository)
    {
        $this->sampleRepository = $sampleRepository;
    }

    /**
     * Read Downloadable Samples for the provided Entity.
     *
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->getTypeId() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $entity;
        }
        $entityExtension = $entity->getExtensionAttributes();
        $samples = $this->sampleRepository->getSamplesByProduct($entity);
        $entityExtension->setDownloadableProductSamples($samples);
        $entity->setExtensionAttributes($entityExtension);

        return $entity;
    }
}
