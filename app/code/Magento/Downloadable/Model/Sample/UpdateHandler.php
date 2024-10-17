<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Downloadable\Model\Sample;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Downloadable\Api\SampleRepositoryInterface as SampleRepository;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * UpdateHandler for downloadable product samples
 */
class UpdateHandler implements ExtensionInterface
{
    private const GLOBAL_SCOPE_ID = 0;

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
     * Update samples for downloadable product if exist
     *
     * @param ProductInterface $entity
     * @param array $arguments
     * @return ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = []): ProductInterface
    {
        if ($entity->getTypeId() === Type::TYPE_DOWNLOADABLE) {
            $this->updateSamples($entity);
        }

        return $entity;
    }

    /**
     * Update product samples
     *
     * @param ProductInterface $entity
     * @return void
     */
    private function updateSamples(ProductInterface $entity): void
    {
        $isGlobalScope = (int) $entity->getStoreId() === self::GLOBAL_SCOPE_ID;
        $samples = $entity->getExtensionAttributes()->getDownloadableProductSamples();
        $oldSamples = $this->sampleRepository->getList($entity->getSku());

        if (!empty($samples)) {
            $updatedSamples = [];
            foreach ($samples as $sample) {
                if ($sample->getId()) {
                    $updatedSamples[$sample->getId()] = true;
                }
                $this->sampleRepository->save($entity->getSku(), $sample, $isGlobalScope);
            }
        }

        foreach ($oldSamples as $sample) {
            if (!isset($updatedSamples[$sample->getId()])) {
                $this->sampleRepository->delete($sample->getId());
            }
        }
    }
}
