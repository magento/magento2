<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Stub abstract class which provide to change protected property "$_disabledAttrs" and test methods depended on it
 */
namespace Magento\ImportExport\Model\Export;

abstract class AbstractStubEntity extends \Magento\ImportExport\Model\Export\AbstractEntity
{
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
        $this->_disabledAttrs = ['default_billing', 'default_shipping'];
    }
}
