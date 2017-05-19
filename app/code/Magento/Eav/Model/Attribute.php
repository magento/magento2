<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * EAV attribute resource model (Using Forms)
 *
 * @method \Magento\Eav\Model\Attribute\Data\AbstractData|null getDataModel() Get data model linked to attribute or null.
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model;

use Magento\Store\Model\Website;

/**
 * @api
 */
class Attribute extends \Magento\Eav\Model\Entity\Attribute
{
    /**
     * Name of the module
     * Override it
     */
    //const MODULE_NAME = 'Magento_Eav';

    /**
     * Prefix of model events object
     *
     * @var string
     */
    protected $_eventObject = 'attribute';

    /**
     * Active Website instance
     *
     * @var Website
     */
    protected $_website;

    /**
     * Set active website instance
     *
     * @param Website|int $website
     * @return $this
     * @codeCoverageIgnore
     */
    public function setWebsite($website)
    {
        $this->_website = $this->_storeManager->getWebsite($website);
        return $this;
    }

    /**
     * Return active website instance
     *
     * @return Website
     */
    public function getWebsite()
    {
        if (is_null($this->_website)) {
            $this->_website = $this->_storeManager->getWebsite();
        }

        return $this->_website;
    }

    /**
     * Processing object after save data
     *
     * @return $this
     */
    public function afterSave()
    {
        $this->_eavConfig->clear();
        return parent::afterSave();
    }

    /**
     * Return forms in which the attribute
     *
     * @return array
     */
    public function getUsedInForms()
    {
        $forms = $this->getData('used_in_forms');
        if (is_null($forms)) {
            $forms = $this->_getResource()->getUsedInForms($this);
            $this->setData('used_in_forms', $forms);
        }
        return $forms;
    }

    /**
     * Return validate rules
     *
     * @return array
     */
    public function getValidateRules()
    {
        $rules = $this->getData('validate_rules');
        if (is_array($rules)) {
            return $rules;
        } elseif (!empty($rules)) {
            return (array)$this->getSerializer()->unserialize($rules);
        }
        return [];
    }

    /**
     * Set validate rules
     *
     * @param array|string $rules
     * @return $this
     */
    public function setValidateRules($rules)
    {
        if (empty($rules)) {
            $rules = null;
        } elseif (is_array($rules)) {
            $rules = $this->getSerializer()->serialize($rules);
        }
        $this->setData('validate_rules', $rules);

        return $this;
    }

    /**
     * Return scope value by key
     *
     * @param string $key
     * @return mixed
     */
    protected function _getScopeValue($key)
    {
        $scopeKey = sprintf('scope_%s', $key);
        if ($this->getData($scopeKey) !== null) {
            return $this->getData($scopeKey);
        }
        return $this->getData($key);
    }

    /**
     * Return is attribute value required
     *
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getIsRequired()
    {
        return $this->_getScopeValue('is_required');
    }

    /**
     * Return is visible attribute flag
     *
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getIsVisible()
    {
        return $this->_getScopeValue('is_visible');
    }

    /**
     * Return default value for attribute
     *
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getDefaultValue()
    {
        return $this->_getScopeValue('default_value');
    }

    /**
     * Return count of lines for multiply line attribute
     *
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getMultilineCount()
    {
        return $this->_getScopeValue('multiline_count');
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        $this->_eavConfig->clear();
        return parent::afterDelete();
    }
}
