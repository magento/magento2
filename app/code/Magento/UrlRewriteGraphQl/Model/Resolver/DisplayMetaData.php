<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Display MetaData page field resolver, used for GraphQL request processing
 */
class DisplayMetaData implements ResolverInterface
{
    const TITLE = 'title';
    const KEYWORD = 'keywords';
    const DESCRIPTION = 'description';

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
        return $this->convertMetaData($value);
    }

    /**
     * Convert Meta Data
     *
     * @param array $value
     * @return array
     */
    private function convertMetaData(Array $value) {
        $metaData = [
            self::TITLE=> $value['meta_title'] ?? null,
            self::KEYWORD=> $value['meta_keywords'] ?? $value['meta_keyword'] ?? null,
            self::DESCRIPTION=> $value['meta_description'] ?? null
        ];
        return $metaData;
    }
}
