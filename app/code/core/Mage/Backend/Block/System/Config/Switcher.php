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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Block_System_Config_Switcher extends Mage_Backend_Block_Template
{
    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('system/config/switcher.phtml');
        return parent::_prepareLayout();
    }

    /**
     * Retrieve list of available stores
     *
     * @return array
     */
    public function getStoreSelectOptions()
    {
        $section = $this->getRequest()->getParam('section');
        $curWebsite = $this->getRequest()->getParam('website');
        $curStore   = $this->getRequest()->getParam('store');

        $storeModel = Mage::getSingleton('Mage_Core_Model_System_Store');
        /* @var $storeModel Mage_Core_Model_System_Store */

        $options = array();
        $options['default'] = array(
            'label'    => $this->helper('Mage_Backend_Helper_Data')->__('Default Config'),
            'url'      => $this->getUrl('*/*/*', array('section' => $section)),
            'selected' => !$curWebsite && !$curStore,
            'style'    => 'background:#ccc; font-weight:bold;',
        );

        foreach ($storeModel->getWebsiteCollection() as $website) {
            $options = $this->_processWebsite($storeModel, $website, $section, $curStore, $curWebsite, $options);
        }

        return $options;
    }

    /**
     * Process website info
     *
     * @param Mage_Core_Model_System_Store $storeModel
     * @param Mage_Core_Model_Website $website
     * @param string $section
     * @param string $curStore
     * @param string $curWebsite
     * @param array $options
     * @return array
     */
    protected function _processWebsite(
        Mage_Core_Model_System_Store $storeModel,
        Mage_Core_Model_Website $website,
        $section,
        $curStore,
        $curWebsite,
        array $options
    ) {
        $websiteShow = false;
        foreach ($storeModel->getGroupCollection() as $group) {
            if ($group->getWebsiteId() != $website->getId()) {
                continue;
            }
            $groupShow = false;
            foreach ($storeModel->getStoreCollection() as $store) {
                if ($store->getGroupId() != $group->getId()) {
                    continue;
                }
                if (!$websiteShow) {
                    $websiteShow = true;
                    $options['website_' . $website->getCode()] = array(
                        'label' => $website->getName(),
                        'url' => $this->getUrl('*/*/*',
                            array('section' => $section, 'website' => $website->getCode())
                        ),
                        'selected' => !$curStore && $curWebsite == $website->getCode(),
                        'style' => 'padding-left:16px; background:#DDD; font-weight:bold;',
                    );
                }
                if (!$groupShow) {
                    $groupShow = true;
                    $options['group_' . $group->getId() . '_open'] = array(
                        'is_group' => true,
                        'is_close' => false,
                        'label' => $group->getName(),
                        'style' => 'padding-left:32px;'
                    );
                }
                $options['store_' . $store->getCode()] = array(
                    'label' => $store->getName(),
                    'url' => $this->getUrl('*/*/*',
                        array('section' => $section, 'website' => $website->getCode(), 'store' => $store->getCode())
                    ),
                    'selected' => $curStore == $store->getCode(),
                    'style' => '',
                );
            }
            if ($groupShow) {
                $options['group_' . $group->getId() . '_close'] = array(
                    'is_group' => true,
                    'is_close' => true,
                );
            }
        }
        return $options;
    }

    /**
     * Return store switcher hint html
     *
     * @return mixed
     */
    public function getHintHtml()
    {
        return Mage::getBlockSingleton('Mage_Backend_Block_Store_Switcher')->getHintHtml();
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            return parent::_toHtml();
        }
        return '';
    }
}
