<?php

namespace Magento\ConfigurableProduct\Plugin\Catalog\Model\Product\Attribute\Backend\TierPrice;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\UpdateHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class UpdateHandlerPlugin
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param ProductAttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param UpdateHandler $subject
     * @param mixed $entity
     * @param array $arguments
     * @return array
     */
    public function beforeExecute(UpdateHandler $subject, $entity, $arguments = [])
    {
        $attribute = $this->attributeRepository->get('tier_price');
        $origPrices = $entity->getOrigData($attribute->getName());
        
        if ($entity->getTypeId() === Configurable::TYPE_CODE && $origPrices !== null) {
            $entity->setData($attribute->getName(), []);
        }

        return [$entity, $arguments];
    }
}
