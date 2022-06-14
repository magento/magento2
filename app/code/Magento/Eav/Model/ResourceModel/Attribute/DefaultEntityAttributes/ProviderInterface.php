<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
