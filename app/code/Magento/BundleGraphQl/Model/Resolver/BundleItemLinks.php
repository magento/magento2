<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver;

use Magento\BundleGraphQl\Model\Resolver\Links\Collection;
use Magento\BundleGraphQl\Model\Resolver\Links\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 */
class BundleItemLinks implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $linkCollectionFactory;

    /**
     * @var ValueFactory
     */
    private ValueFactory $valueFactory;

    /**
     * @param Collection $linkCollection Deprecated. Use $linkCollectionFactory instead
     * @param ValueFactory $valueFactory
     * @param CollectionFactory|null $linkCollectionFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Collection $linkCollection,
        ValueFactory $valueFactory,
        ?CollectionFactory $linkCollectionFactory = null
    ) {
        $this->linkCollectionFactory = $linkCollectionFactory
            ?: ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->valueFactory = $valueFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['option_id']) || !isset($value['parent_id'])) {
            throw new LocalizedException(__('"option_id" and "parent_id" values should be specified'));
        }
        $linkCollection = $this->linkCollectionFactory->create();
        $linkCollection->addIdFilters((int)$value['option_id'], (int)$value['parent_id']);
        $result = function () use ($value, $linkCollection) {
            return $linkCollection->getLinksForOptionId((int)$value['option_id']);
        };
        return $this->valueFactory->create($result);
    }
}
