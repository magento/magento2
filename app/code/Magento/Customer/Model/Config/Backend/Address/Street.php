<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model\Config\Backend\Address;

/**
 * Line count config model for customer address street attribute
 *
 * @method string getWebsiteCode
 */
class Street extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /** @var \Magento\Framework\StoreManagerInterface */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
    }

    /**
     * Actions after save
     *
     * @return $this
     */
    protected function _afterSave()
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

            case \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT:
                $attribute->setData('multiline_count', $value);
                break;
        }
        $attribute->save();
        return $this;
    }

    /**
     * Processing object after delete data
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function _afterDelete()
    {
        $result = parent::_afterDelete();

        if ($this->getScope() == \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES) {
            $attribute = $this->_eavConfig->getAttribute('customer_address', 'street');
            $website = $this->_storeManager->getWebsite($this->getScopeCode());
            $attribute->setWebsite($website);
            $attribute->load($attribute->getId());
            $attribute->setData('scope_multiline_count', null);
            $attribute->save();
        }

        return $result;
    }
}
