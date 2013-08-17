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
 * @package     Mage_Backup
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backup grid item renderer
 *
 * @category   Mage
 * @package    Mage_Backup
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backup_Block_Adminhtml_Grid_Column_Renderer_Download
    extends Mage_Backend_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Renders grid column
     *
     * @param Varien_Object $row
     * @return mixed
     */
    public function _getValue(Varien_Object $row)
    {
        $url7zip = $this->helper('Mage_Adminhtml_Helper_Data')
            ->__('The archive can be uncompressed with <a href="%s">%s</a> on Windows systems.', 'http://www.7-zip.org/',
            '7-Zip');

        return '<a href="' . $this->getUrl('*/*/download',
            array('time' => $row->getData('time'), 'type' => $row->getData('type'))) . '">' . $row->getData('extension')
               . '</a> &nbsp; <small>(' . $url7zip . ')</small>';

    }
}