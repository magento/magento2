<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewriteGraphQl\Model;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * @inheritdoc
 */
class CmsPageTypeResolver implements TypeResolverInterface
{
    const CMS_PAGE = 'CMS_PAGE';
    const TYPE_RESOLVER = 'CmsPage';

    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['type_id']) && $data['type_id'] == self::CMS_PAGE) {
            return self::TYPE_RESOLVER;
        }
        return '';
    }
}
