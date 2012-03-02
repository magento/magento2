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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect Template model
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Template extends Mage_Core_Model_Template
{
    /**
     * Model constructor
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_init('Mage_XmlConnect_Model_Resource_Template');
    }

    /**
     * Processing object before save data
     * Add created_at  and modified_at params
     *
     * @return Mage_XmlConnect_Model_Template
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        $currentDate = Mage::getSingleton('Mage_Core_Model_Date')->gmtDate();
        if (!$this->getId()) {
            $this->setCreatedAt($currentDate);
        }
        $this->setModifiedAt($currentDate);

        return $this;
    }

    /**
     * Get template type
     *
     * @return int
     */
    public function getType()
    {
        return self::TYPE_HTML;
    }

    /**
     * Retrieve processed template
     *
     * @param array $variables
     * @return string
     */
    public function getProcessedTemplate(array $variables = array())
    {
        /* @var $processor Mage_Widget_Model_Template_Filter */
        $processor = Mage::getModel('Mage_Widget_Model_Template_Filter');

        $variables['this'] = $this;

        if (Mage::app()->isSingleStoreMode()) {
            $processor->setStoreId(Mage::app()->getStore());
        } else {
            $processor->setStoreId(1);
        }

        $htmlDescription = <<<EOT
<div style="font-size: 0.8em; text-decoration: underline; margin-top: 1.5em; line-height: 2em;">%s:</div>
EOT;
        $html  = sprintf($htmlDescription, Mage::helper('Mage_XmlConnect_Helper_Data')->__('Push title'))
                    . $this->getPushTitle();
        $html .= sprintf($htmlDescription, Mage::helper('Mage_XmlConnect_Helper_Data')->__('Message title'))
                    . $this->getMessageTitle();
        $html .= sprintf($htmlDescription, Mage::helper('Mage_XmlConnect_Helper_Data')->__('Message content'))
                    . $processor->filter($this->getContent());

        return $html;
    }
}
