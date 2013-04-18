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
 * Available theme list
 *
 * @method int getNextPage()
 * @method Mage_DesignEditor_Block_Adminhtml_Theme_Selector_List_Available setNextPage(int $page)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_DesignEditor_Block_Adminhtml_Theme_Selector_List_Available
    extends Mage_DesignEditor_Block_Adminhtml_Theme_Selector_List_Abstract
{
    /**
     * @var Mage_Core_Model_Theme_Service
     */
    protected $_serviceModel;

    /**
     * @param Mage_Core_Block_Template_Context $context
     * @param Mage_Core_Model_App $app
     * @param Mage_Core_Model_Theme_Service $serviceModel
     * @param array $data
     */
    public function __construct(
        Mage_Core_Block_Template_Context $context,
        Mage_Core_Model_App $app,
        Mage_Core_Model_Theme_Service $serviceModel,
        array $data = array()
    ) {
        $this->_serviceModel = $serviceModel;

        parent::__construct($context, $app, $data
        );
    }

    /**
     * Get service model
     *
     * @return Mage_Core_Model_Theme_Service
     */
    protected function _getServiceModel()
    {
        return $this->_serviceModel;
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Available theme list');
    }

    /**
     * Get next page url
     *
     * @return string
     */
    public function getNextPageUrl()
    {
        return $this->getNextPage() <= $this->getCollection()->getLastPageNumber()
            ? $this->getUrl('*/*/*', array('page' => $this->getNextPage()))
            : '';
    }

    /**
     * Get demo button
     *
     * @param Mage_DesignEditor_Block_Adminhtml_Theme $themeBlock
     * @return Mage_DesignEditor_Block_Adminhtml_Theme_Selector_List_Available
     */
    protected function _addDemoButtonHtml($themeBlock)
    {
        /** @var $demoButton Mage_Backend_Block_Widget_Button */
        $demoButton = $this->getLayout()->createBlock('Mage_Backend_Block_Widget_Button');
        $demoButton->setData(array(
            'label'     => $this->__('Theme Demo'),
            'class'     => 'action-theme-preview',
            'data_attribute' => array(
                'mage-init' => array(
                    'button' => array(
                        'event' => 'preview',
                        'target' => 'body',
                        'eventData' => array(
                            'preview_url' => $this->_getPreviewUrl($themeBlock->getTheme()->getId())
                        )
                    ),
                ),
            )
        ));

        $themeBlock->addButton($demoButton);
        return $this;
    }

    /**
     * Add theme buttons
     *
     * @param Mage_DesignEditor_Block_Adminhtml_Theme $themeBlock
     * @return Mage_DesignEditor_Block_Adminhtml_Theme_Selector_List_Abstract
     */
    protected function _addThemeButtons($themeBlock)
    {
        parent::_addThemeButtons($themeBlock);

        $this->_addDemoButtonHtml($themeBlock)->_addAssignButtonHtml($themeBlock);

        if ($this->_getServiceModel()->isCustomizationsExist()) {
            $this->_addEditButtonHtml($themeBlock);
        }

        return $this;
    }
}
