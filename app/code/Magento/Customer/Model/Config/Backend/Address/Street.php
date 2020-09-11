<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config\Backend\Address;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Line count config model for customer address street attribute
 *
 * @method string getWebsiteCode()
 */
class Street extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
    }

    /**
     * Actions after save
     *
     * @return $this
     */
    public function afterSave()
    {
        $attribute = $this->_eavConfig->getAttribute('customer_address', 'street');
        $value = $this->getValue();
        switch ($this->getScope()) {
            case \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES:
                $website = $this->_storeManager->getWebsite($this->getScopeCode());
                $attribute->setWebsite($website);
                $attribute->load($attribute->getId());
                if ($attribute->getData('multiline_count') != $value) {
                    $attribute->setData('scope_multiline_count', $value);
                }
                break;

            case ScopeConfigInterface::SCOPE_TYPE_DEFAULT:
                $attribute->setData('multiline_count', $value);
                break;
        }
        $attribute->save();
        return parent::afterSave();
    }

    /**
     * Processing object after delete data
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function afterDelete()
    {
        $result = parent::afterDelete();

        $attribute = $this->_eavConfig->getAttribute('customer_address', 'street');
        switch ($this->getScope()) {
            case \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES:
                $website = $this->_storeManager->getWebsite($this->getScopeCode());
                $attribute->setWebsite($website);
                $attribute->load($attribute->getId());
                $attribute->setData('scope_multiline_count', null);
                break;

            case ScopeConfigInterface::SCOPE_TYPE_DEFAULT:
                $attribute->setData('multiline_count', 2);
                break;
        }
        $attribute->save();
        return $result;
    }
}
