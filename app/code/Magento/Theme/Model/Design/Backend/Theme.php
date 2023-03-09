<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Backend;

use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;

class Theme extends Value
{
    /**
     * Design package instance
     *
     * @var DesignInterface
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
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param DesignInterface $design
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        DesignInterface $design,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
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
            $design->setDesignTheme($this->getValue(), Area::AREA_FRONTEND);
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
                ScopeInterface::SCOPE_STORE
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
