<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\Catalog\Model\Product\Attribute\Backend\TierPrice;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\UpdateHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Plugin for handling tier prices during product attribute backend update.
 */
class UpdateHandlerPlugin
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private readonly ProductAttributeRepositoryInterface $attributeRepository;

    /**
     * UpdateHandlerPlugin constructor.
     *
     * @param ProductAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(ProductAttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Before execute plugin.
     *
     * @param UpdateHandler $subject
     * @param mixed $entity
     * @param array $arguments
     * @return array
     */
    public function beforeExecute(UpdateHandler $subject, $entity, $arguments = []): array
    {
        $attribute = $this->attributeRepository->get('tier_price');
        $origPrices = $entity->getOrigData($attribute->getName());

        if ($entity->getTypeId() === Configurable::TYPE_CODE && $origPrices !== null) {
            $entity->setData($attribute->getName(), []);
        }

        return [$entity, $arguments];
    }
}
