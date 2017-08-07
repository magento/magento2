<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\SampleRepositoryInterface as SampleRepository;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class CreateHandler
 * @since 2.1.0
 */
class CreateHandler implements ExtensionInterface
{
    /**
     * @var SampleRepository
     * @since 2.1.0
     */
    protected $sampleRepository;

    /**
     * @param SampleRepository $sampleRepository
     * @since 2.1.0
     */
    public function __construct(SampleRepository $sampleRepository)
    {
        $this->sampleRepository = $sampleRepository;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function execute($entity, $arguments = [])
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->getTypeId() != Type::TYPE_DOWNLOADABLE) {
            return $entity;
        }

        /** @var \Magento\Downloadable\Api\Data\SampleInterface[] $samples */
        $samples = $entity->getExtensionAttributes()->getDownloadableProductSamples() ?: [];
        foreach ($samples as $sample) {
            $sample->setId(null);
            $this->sampleRepository->save($entity->getSku(), $sample, !(bool)$entity->getStoreId());
        }

        return $entity;
    }
}
