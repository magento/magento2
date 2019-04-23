<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Backend;

use Magento\Framework\Unserialize\SecureUnserializer;
use Magento\Framework\App\ObjectManager;

class Serialized extends \Magento\Framework\App\Config\Value
{
    /**
     * @var SecureUnserializer
     */
    private $unserializer;

    /**
     * Serialized constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param SecureUnserializer|null $unserializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        SecureUnserializer $unserializer = null
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->unserializer = $unserializer ?: ObjectManager::getInstance()->get(SecureUnserializer::class);
    }

    /**
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if (!is_array($value)) {
            try {
                $this->setValue(empty($value) ? false : $this->unserializer->unserialize($value));
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $this->setValue(false);
            }
        }
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            $this->setValue(serialize($value));
        }
        parent::beforeSave();
        return $this;
    }
}
