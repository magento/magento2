<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Model\System\Config\Backend;

/**
 * Cache cleaner backend model
 *
 */
class Links extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_cacheTypeList = $cacheTypeList;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Invalidate cache type, when value was changed
     *
     * @return void
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->_cacheTypeList->invalidate(\Magento\Framework\View\Element\AbstractBlock::CACHE_GROUP);
        }
    }
}
