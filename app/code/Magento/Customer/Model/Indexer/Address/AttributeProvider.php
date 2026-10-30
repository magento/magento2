<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Model\Indexer\Address;

use Magento\Customer\Model\ResourceModel\Address\Attribute\Collection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;

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
