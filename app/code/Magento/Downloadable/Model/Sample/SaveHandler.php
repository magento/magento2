<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\SampleRepositoryInterface as SampleRepository;

/**
 * Class SaveHandler
 */
class SaveHandler
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
     * @param string $entityType
     * @param object $entity
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($this->sampleRepository->getList($entity->getSku()) as $sample) {
            $this->sampleRepository->delete($sample->getId());
        }
        $samples = $entity->getExtensionAttributes()->getDownloadableProductSamples() ?: [];
        foreach ($samples as $sample) {
            $this->sampleRepository->save($entity->getSku(), $sample, !(bool)$entity->getStoreId());
        }
        return $entity;
    }
}
