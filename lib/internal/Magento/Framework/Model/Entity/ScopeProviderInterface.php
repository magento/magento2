<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Entity;

/**
 * Class ScopeProviderInterface
 *
 * @api
 */
interface ScopeProviderInterface
{
    /**
     * @param string $entityType
     * @param array $entityData
     * @return mixed
     */
    public function getContext($entityType, $entityData = []);
}
