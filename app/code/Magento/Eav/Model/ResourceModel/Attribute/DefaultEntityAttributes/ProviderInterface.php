<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes;

/**
 * Interface \Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes\ProviderInterface
 *
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
