<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Plugin\Model\Customer\Metadata;

use Magento\Eav\Model\Config;

class AddressMetadata
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * Initialize eav config model
     *
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Reset the eav attributes
     *
     * @return void
     */
    public function beforeGetAllAttributesMetadata()
    {
        $this->eavConfig->clear();
    }
}
