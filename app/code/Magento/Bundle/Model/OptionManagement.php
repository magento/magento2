<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

use Magento\Framework\Exception\InputException;

/**
 * Class \Magento\Bundle\Model\OptionManagement
 *
 * @since 2.0.0
 */
class OptionManagement implements \Magento\Bundle\Api\ProductOptionManagementInterface
{
    /**
     * @var \Magento\Bundle\Api\ProductOptionRepositoryInterface
     * @since 2.0.0
     */
    protected $optionRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     * @since 2.0.0
     */
    protected $productRepository;

    /**
     * @param \Magento\Bundle\Api\ProductOptionRepositoryInterface $optionRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Bundle\Api\ProductOptionRepositoryInterface $optionRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->optionRepository = $optionRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save(\Magento\Bundle\Api\Data\OptionInterface $option)
    {
        $product = $this->productRepository->get($option->getSku(), true);
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new InputException(__('Only implemented for bundle product'));
        }
        return $this->optionRepository->save($product, $option);
    }
}
