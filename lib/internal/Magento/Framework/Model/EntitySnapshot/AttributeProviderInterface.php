<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\EntitySnapshot;

/**
 * Interface AttributeProviderInterface
 *
 * @api
 */
interface AttributeProviderInterface
{
    /**
     * Returns array of fields
     *
     * @param string $entityType
     * @return string[]
     */
    public function getAttributes($entityType);
}
