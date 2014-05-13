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
    protected function _beforeSave()
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
