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
 * Logo uploader element renderer
 *
 * @todo Temporary solution.
 * Discuss logo uploader with PO and remove this method.
 * Logo should be assigned to store view level, but not theme.
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Form_Renderer_LogoUploader
    extends Mage_DesignEditor_Block_Adminhtml_Editor_Form_Renderer_ImageUploader
{
    /**
     * Set of templates to render
     *
     * Upper is rendered first and is inserted into next using <?php echo $this->getHtml() ?>
     *
     * @var array
     */
    protected $_templates = array(
        'Mage_DesignEditor::editor/form/renderer/element/input.phtml',
        'Mage_DesignEditor::editor/form/renderer/logo-uploader.phtml',
    );

    /**
     * Return theme identification number
     *
     * @return int|null
     */
    protected function getThemeId()
    {
        /** @var $helper Mage_DesignEditor_Helper_Data */
        $helper = $this->_helperFactory->get('Mage_DesignEditor_Helper_Data');
        return $helper->getVirtualThemeId();
    }

    /**
     * Get logo upload url
     *
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getLogoUploadUrl($store)
    {
        return $this->getUrl('*/system_design_editor_tools/uploadStoreLogo',
            array('theme_id' => $this->getThemeId(), 'store_id' => $store->getId())
        );
    }

    /**
     * Get logo upload url
     *
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getLogoRemoveUrl($store)
    {
        return $this->getUrl('*/system_design_editor_tools/removeStoreLogo',
            array('theme_id' => $this->getThemeId(), 'store_id' => $store->getId())
        );
    }

    /**
     * Get logo image
     *
     * @param Mage_Core_Model_Store $store
     * @return string|bool
     */
    public function getLogoImage($store)
    {
        return (null !== $store) ? $this->_storeConfig->getConfig('design/header/logo_src', $store->getId()) : null;
    }

    /**
     * Get stores list
     *
     * @return mixed
     */
    public function getStoresList()
    {
        $stores = Mage::getObjectManager()->get('Mage_Core_Model_Theme_Service')->getStoresByThemes();
        return isset($stores[$this->getThemeId()])
            ? $stores[$this->getThemeId()]
            : null;
    }
}
