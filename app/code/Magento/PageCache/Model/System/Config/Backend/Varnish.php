<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\System\Config\Backend;

/**
 * Backend model for processing Varnish settings
 *
 * Class Varnish
 */
class Varnish extends \Magento\Framework\App\Config\Value
{
    /**
     * @var array
     */
    protected $defaultValues;

    /**
     * Set default data if empty fields have been left
     *
     * @return $this|\Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Model\Exception
     */
    public function beforeSave()
    {
        $data = $this->_getDefaultValues();
        $currentValue = $this->getValue();
        if (!$currentValue) {
            $replaceValue = isset($data[$this->getField()]) ? $data[$this->getField()] : false;
            $this->setValue($replaceValue);
        }
        return $this;
    }

    /**
     * Get Default Config Values
     *
     * @return array
     */
    protected function _getDefaultValues()
    {
        if (!$this->defaultValues) {
            $this->defaultValues = $this->_config->getValue('system/full_page_cache/default');
        }
        return $this->defaultValues;
    }

    /**
     * If fields are empty fill them with default data
     *
     * @return $this|\Magento\Framework\Model\AbstractModel
     */
    protected function _afterLoad()
    {
        $data = $this->_getDefaultValues();
        $currentValue = $this->getValue();
        if (!$currentValue) {
            foreach ($data as $field => $value) {
                if (strstr($this->getPath(), $field)) {
                    $this->setValue($value);
                    $this->save();
                    break;
                }
            }
        }
        return $this;
    }
}
