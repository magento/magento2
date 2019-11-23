<?php
declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\ResourceModel\Setup;

use Magento\Eav\Model\Entity\Setup\PropertyMapperAbstract;

/**
 * Class PropertyMapper
 *
 * @package Magento\CatalogSearch\Model\ResourceModel\Setup
 */
class PropertyMapper extends PropertyMapperAbstract
{
    /**
     * Map input attribute properties to storage representation
     *
     * @param array $input
     * @param int $entityTypeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map(array $input, $entityTypeId): array
    {
        return [
            'search_weight' => $this->_getValue($input, 'search_weight', 1),
        ];
    }
}
