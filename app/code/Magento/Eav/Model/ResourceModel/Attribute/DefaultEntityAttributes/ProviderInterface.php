<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes;

interface ProviderInterface
{
    /**
     * Retrieve default entity static attributes
     *
     * @return string[]
     */
    public function getDefaultAttributes();
}
