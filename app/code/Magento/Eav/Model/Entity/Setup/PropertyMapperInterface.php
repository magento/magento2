<?php
/**
 * Attribute property mapper interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Entity\Setup;

/**
 * Interface \Magento\Eav\Model\Entity\Setup\PropertyMapperInterface
 *
 * @api
 */
interface PropertyMapperInterface
{
    /**
     * Map input attribute properties to storage representation
     *
     * @param array $input
     * @param int $entityTypeId
     * @return array
     */
    public function map(array $input, $entityTypeId);
}
