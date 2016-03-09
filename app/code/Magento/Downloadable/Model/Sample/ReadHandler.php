<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\SampleRepositoryInterface as SampleRepository;

/**
 * Class ReadHandler
 */
class ReadHandler
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
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->getTypeId() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $entity;
        }
        $entityExtension = $entity->getExtensionAttributes();
        $samples = $this->sampleRepository->getSamplesByProduct($entity);
        if ($samples) {
            $entityExtension->setDownloadableProductSamples($samples);
        }
        $entity->setExtensionAttributes($entityExtension);
        return $entity;
    }
}
