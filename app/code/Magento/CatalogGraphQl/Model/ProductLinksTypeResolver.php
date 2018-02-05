<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model;

use Magento\Framework\GraphQl\Config\Data\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class ProductLinksTypeResolver implements TypeResolverInterface
{
    /**
     * @var string[]
     */
    private $linkTypes = ['related', 'upsell', 'crosssell'];

    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data)
    {
        if (isset($data['type_id'])) {
            $linkType = $data['link_type'];
            if (in_array($linkType, $this->linkTypes)) {
                return 'ProductLinks';
            }
        }
    }
}
