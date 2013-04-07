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
 * Color-picker form element renderer
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Form_Renderer_BackgroundUploader
    extends Mage_DesignEditor_Block_Adminhtml_Editor_Form_Renderer
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Mage_DesignEditor::editor/form/renderer/background-uploader.phtml';

    /**
     * Return theme identification number
     *
     * @return int|null
     */
    protected function getThemeId()
    {
        /** @var $helper Mage_DesignEditor_Helper_Data */
        $helper = $this->_helperFactory->get('Mage_DesignEditor_Helper_Data');
        return $helper->getEditableThemeId();
    }

    /**
     * Get URL of image upload action
     *
     * @return string
     */
    public function getImageUploadUrl()
    {
        return $this->getUrl('*/system_design_editor_tools/uploadQuickStyleImage',
            array('theme_id' => $this->getThemeId())
        );
    }

    /**
     * Get URL of remove image action
     *
     * @return string
     */
    public function getImageRemoveUrl()
    {
        return $this->getUrl('*/system_design_editor_tools/removeQuickStyleImage',
            array('theme_id' => $this->getThemeId())
        );
    }
}
