<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Indexer\Address;

use Magento\Eav\Model\Config;

class AttributeProvider extends \Magento\Customer\Model\Indexer\AttributeProvider
{
    /**
     * EAV entity
     */
    const ENTITY = 'customer_address';

    /**
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        parent::__construct($eavConfig);
    }
}
