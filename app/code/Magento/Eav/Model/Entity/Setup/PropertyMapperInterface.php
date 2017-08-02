<?php
/**
 * Attribute property mapper interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Setup;

/**
 * Interface \Magento\Eav\Model\Entity\Setup\PropertyMapperInterface
 *
 * @since 2.0.0
 */
interface PropertyMapperInterface
{
    /**
     * Map input attribute properties to storage representation
     *
     * @param array $input
     * @param int $entityTypeId
     * @return array
     * @since 2.0.0
     */
    public function map(array $input, $entityTypeId);
}
