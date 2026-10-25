<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
