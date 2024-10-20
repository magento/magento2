<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Model\Config\Backend;

use Magento\Framework\Model\AbstractModel;

/**
 * Backend model for shipping table rates CSV importing
 */
class Tablerate extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\OfflineShipping\Model\ResourceModel\Carrier\TablerateFactory
     */
    protected $_tablerateFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\OfflineShipping\Model\ResourceModel\Carrier\TablerateFactory $tablerateFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\OfflineShipping\Model\ResourceModel\Carrier\TablerateFactory $tablerateFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_tablerateFactory = $tablerateFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * After save
     *
     * @return $this
     */
    public function afterSave()
    {
        /** @var \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate $tableRate */
        $tableRate = $this->_tablerateFactory->create();
        $tableRate->uploadAndImport($this);
        return parent::afterSave();
    }
}
