<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\System\Config\Backend\Rss;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Category extends ConfigValue
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param ProductAttributeRepositoryInterface|null $productAttributeRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = [],
        ?ProductAttributeRepositoryInterface $productAttributeRepository = null
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->productAttributeRepository = $productAttributeRepository ??
            ObjectManager::getInstance()->get(ProductAttributeRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function afterSave()
    {
        if ($this->isValueChanged() && $this->getValue()) {
            $updatedAtAttr = $this->productAttributeRepository->get(ProductInterface::UPDATED_AT);
            if (!$updatedAtAttr->getUsedForSortBy()) {
                $updatedAtAttr->setUsedForSortBy(true);
                $this->productAttributeRepository->save($updatedAtAttr);
            }
        }

        return parent::afterSave();
    }
}
