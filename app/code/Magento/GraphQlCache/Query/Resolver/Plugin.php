<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Query\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQlCache\Model\CacheTags;

class Plugin
{
    /**
     * @var CacheTags
     */
    private $cacheTags;

    /**
     * @param CacheTags $cacheTags
     */
    public function __construct(CacheTags $cacheTags)
    {
        $this->cacheTags = $cacheTags;
    }

    public function afterResolve(
        ResolverInterface $subject,
        $resolvedValue,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($field->getName() == 'products') {
            // TODO: Read cache tag value from the GraphQL schema and make it accessible via $field
            $cacheTag = 'cat_p';
        }
        // TODO: Can be optimized to avoid tags calculation for POST requests
        if (!empty($cacheTag)) {
            $cacheTags = [$cacheTag];
            // Resolved value must have cache IDs defined
            $resolvedItemsIds = $this->extractResolvedItemsIds($resolvedValue);
            foreach ($resolvedItemsIds as $itemId) {
                $cacheTags[] = $cacheTag . '_' . $itemId;
            }
            $this->cacheTags->addCacheTags($cacheTags);
        }
        return $resolvedValue;
    }

    private function extractResolvedItemsIds($resolvedValue)
    {
        // TODO: Implement safety checks and think about additional places which can hold items IDs
        if (isset($resolvedValue['ids']) && is_array($resolvedValue['ids'])) {
            return $resolvedValue['ids'];
        }
        if (isset($resolvedValue['items']) && is_array($resolvedValue['items'])) {
            return array_keys($resolvedValue['items']);
        }
        $ids = [];
        if (is_array($resolvedValue)) {
            foreach ($resolvedValue as $item) {
                if (isset($item['id'])) {
                    $ids[] = $item['id'];
                }
            }
        }
    }
}
