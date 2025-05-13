<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Backend;

use Magento\Framework\App\Config\Value;

class Theme extends Value
{
    /**
     * Design package instance
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design = null;

    /**
     * Path to config node with list of caches
     *
     * @var string
     */
    const XML_PATH_INVALID_CACHES = 'design/invalid_caches';

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\View\DesignInterface $design,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_design = $design;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate specified value against frontend area
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ('' != $this->getValue()) {
            $design = clone $this->_design;
            $design->setDesignTheme($this->getValue(), \Magento\Framework\App\Area::AREA_FRONTEND);
        }
        return parent::beforeSave();
    }

    /**
     * Invalidate cache
     *
     * @param bool $forceInvalidate
     * @return void
     */
    protected function invalidateCache($forceInvalidate = false)
    {
        $types = array_keys(
            $this->_config->getValue(
                self::XML_PATH_INVALID_CACHES,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        if ($forceInvalidate || $this->isValueChanged()) {
            $this->cacheTypeList->invalidate($types);
        }
    }

    /**
     * @return array
     */
    public function getValue()
    {
        return $this->getData('value') !== null ? $this->getData('value') : '';
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}. In addition, it sets status 'invalidate' for blocks and other output caches
     *
     * @return $this
     */
    public function afterSave()
    {
        $this->invalidateCache();
        return parent::afterSave();
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        $this->invalidateCache(true);
        return parent::afterDelete();
    }
}
