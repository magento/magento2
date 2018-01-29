<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProductGraphQl\Model;

use Magento\Framework\GraphQl\Config\Data\TypeResolverInterface;
use Magento\Framework\Exception\InputException;

/**
 * {@inheritdoc}
 */
class GroupedProductLinksTypeResolver implements TypeResolverInterface
{
    /**
     * @var string[]
     */
    private $linkTypes = ['associated'];

    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data)
    {
        $linkType = $data['link_type'];
        if (in_array($linkType, $this->linkTypes)) {
            return 'GroupedProductLinks';
        }

        return null;
    }
}
