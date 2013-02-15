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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block that renders group of files
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Css_Group extends Mage_Backend_Block_Widget_Form
{
    /**
     * Get url to download CSS file
     *
     * @param string $fileId
     * @param int $themeId
     * @return string
     */
    public function getDownloadUrl($fileId, $themeId)
    {
        return $this->getUrl('*/system_design_theme/downloadCss', array(
            'theme_id' => $themeId,
            'file'     => $this->_helperFactory->get('Mage_Theme_Helper_Data')->urlEncode($fileId)
        ));
    }

    /**
     * Check if files group needs "add" button
     *
     * @return bool
     */
    public function hasAddButton()
    {
        return false;
    }

    /**
     * Check if files group needs download buttons next to each file
     *
     * @return bool
     */
    public function hasDownloadButton()
    {
        return true;
    }
}
