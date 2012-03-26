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
 * @package     Mage_Widget
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * A chooser for container for widget instances
 */
class Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Container extends Mage_Core_Block_Html_Select
{
    /**
     * Assign attributes for the HTML select element
     */
    protected function _construct()
    {
        $this->setName('block');
        $this->setClass('required-entry select');
        $this->setExtraParams('onchange="WidgetInstance.loadSelectBoxByType(\'block_template\','
            . ' this.up(\'div.group_container\'), this.value)"');
    }

    /**
     * Add necessary options
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        if (!$this->getOptions()) {
            $layoutUpdateParams = array(
                'area'    => $this->getArea(),
                'package' => $this->getPackage(),
                'theme'   => $this->getTheme(),
            );
            /** @var $layoutUpdate Mage_Core_Model_Layout_Update */
            $layoutUpdate = Mage::getModel('Mage_Core_Model_Layout_Update', $layoutUpdateParams);
            $layoutUpdate->addPageHandles(array($this->getLayoutHandle()));
            $layoutUpdate->load();

            $containers = $layoutUpdate->getContainers();
            if ($this->getAllowedContainers()) {
                foreach (array_keys($containers) as $containerName) {
                    if (!in_array($containerName, $this->getAllowedContainers())) {
                        unset($containers[$containerName]);
                    }
                }
            }
            asort($containers, SORT_STRING);

            $this->addOption('', Mage::helper('Mage_Widget_Helper_Data')->__('-- Please Select --'));
            foreach ($containers as $containerName => $containerLabel) {
                $this->addOption($containerName, $containerLabel);
            }
        }
        return parent::_beforeToHtml();
    }
}
