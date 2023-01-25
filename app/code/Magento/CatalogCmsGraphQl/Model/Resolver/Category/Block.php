<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCmsGraphQl\Model\Resolver\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CmsGraphQl\Model\Resolver\DataProvider\Block as BlockProvider;

/**
 * Resolver category cms content
 */
class Block implements ResolverInterface
{
    /**
     * @var BlockProvider
     */
    private $blockProvider;

    /**
     * @param BlockProvider $blockProvider
     */
    public function __construct(BlockProvider $blockProvider)
    {
        $this->blockProvider = $blockProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Category $category */
        $category = $value['model'];
        $blockId = (int)$category->getLandingPage();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        if (empty($blockId)) {
            return null;
        }

        try {
            $blockData = $this->blockProvider->getBlockById($blockId, $storeId);
        } catch (NoSuchEntityException $e) {
            return new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $blockData;
    }
}
