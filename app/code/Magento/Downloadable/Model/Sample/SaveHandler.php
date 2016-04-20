<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\SampleRepositoryInterface as SampleRepository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
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
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity, $arguments = [])
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->getTypeId() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $entity;
        }

        $oldSamples = $this->sampleRepository->getList($entity->getSku());
        $samples = $entity->getExtensionAttributes()->getDownloadableProductSamples() ?: [];
        $updatedSampleIds = [];
        foreach ($samples as $sample) {
            if ($sample->getId()) {
                $updatedSampleIds[] = $sample->getId();
            }
            $this->sampleRepository->save($entity->getSku(), $sample, !(bool)$entity->getStoreId());
        }
        foreach ($oldSamples as $sample) {
            if (!in_array($sample->getId(), $updatedSampleIds)) {
                $this->sampleRepository->delete($sample->getId());
            }
        }

        return $entity;
    }
}
