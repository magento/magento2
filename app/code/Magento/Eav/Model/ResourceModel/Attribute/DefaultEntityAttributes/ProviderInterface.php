<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes;

/**
 * Interface \Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes\ProviderInterface
 *
 * @api
 */
interface ProviderInterface
{
    /**
     * Retrieve default entity static attributes
     *
     * @return string[]
     */
    public function getDefaultAttributes();
}
