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
 * @category    Mage
 * @package     Mage_GoogleOptimizer
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Optimizer Product Model
 *
 * @category   Mage
 * @package    Mage_GoogleOptimizer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleOptimizer_Model_Code_Product extends Mage_GoogleOptimizer_Model_Code
{
    const DEFAULT_COUNT_OF_ATTRIBUTES = 8;
    protected $_entityType = 'product';

    protected function _afterLoad()
    {
        if ($data = $this->getAdditionalData()) {
            $data = unserialize($data);
            if (isset($data['attributes'])) {
                $this->setAttributes($data['attributes']);
            }
        }
        return parent::_afterLoad();
    }

    protected function _beforeSave()
    {
        if (!($attributes = $this->getData('attributes'))) {
            $attributes = array();
        }
        $this->setData('additional_data', serialize(array(
                'attributes' => $attributes))
            );
        parent::_beforeSave();
    }

    protected function _validate()
    {
        $_validationResult = parent::_validate();
        if (!$_validationResult) {
            return false;
        }
        $attributesFlag = false;
        if ($attributes = $this->getAttributes()) {
            $attributesCount = 0;
            foreach ($attributes as $_attributeId => $_attributeValue) {
                if ($_attributeValue != '') {
                    $attributesCount++;
                }
            }
            if ($attributesCount && $attributesCount <= self::DEFAULT_COUNT_OF_ATTRIBUTES) {
                $attributesFlag = true;
            }
        }
        if ($this->_validateEntryFlag && !$attributesFlag) {
            return false;
        }
        if (!$this->_validateEntryFlag && $attributesFlag) {
            return false;
        }
        return true;
    }

    /**
     * Return empty array if attributes is not defined
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($attributes = $this->_getData('attributes')) {
            return $attributes;
        }
        return array();
    }
}
