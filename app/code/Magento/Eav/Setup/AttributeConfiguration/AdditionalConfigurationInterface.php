<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Setup\AttributeConfiguration;

interface AdditionalConfigurationInterface
{
    /**
     * @return array
     */
    public function toArray();

    /**
     * @return void
     */
    public function __clone();
}
