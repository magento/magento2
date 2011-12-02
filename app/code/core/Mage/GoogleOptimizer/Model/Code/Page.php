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
 * Google Optimizer Cms Page Model
 *
 * @category   Mage
 * @package    Mage_GoogleOptimizer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleOptimizer_Model_Code_Page extends Mage_GoogleOptimizer_Model_Code
{
    const PAGE_TYPE_VARIANT = 'variant';
    protected $_entityType = 'page';

    protected function _afterLoad()
    {
        if ($data = $this->getAdditionalData()) {
            $data = unserialize($data);
            if (isset($data['page_type'])) {
                $this->setPageType($data['page_type']);
            }
        }
        return parent::_afterLoad();
    }

    protected function _beforeSave()
    {

        if ($pageType = $this->getData('page_type')) {
            $this->setData('additional_data', serialize(array(
                'page_type' => $pageType))
            );
        }
        parent::_beforeSave();
    }

    protected function _validate()
    {
        if ($this->getPageType() && $this->getPageType() == self::PAGE_TYPE_VARIANT) {
            if ($this->getTrackingScript()) {
                return true;
            }
        }
        return parent::_validate();
    }

}
