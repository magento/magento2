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
 * @category    Magento
 * @package     Magento_Tax
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Admin tax rate save toolbar
 *
 * @category   Magento
 * @package     Magento_Tax
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Block\Adminhtml\Rate\Toolbar;

class Save extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'toolbar/rate/save.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->assign('createUrl', $this->getUrl('tax/rate/save'));
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'backButton',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Back'),
                'onclick' => 'window.location.href=\'' . $this->getUrl('tax/*/') . '\'',
                'class' => 'back'
            )
        );

        $this->addChild(
            'resetButton',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Reset'), 'onclick' => 'window.location.reload()')
        );

        $this->addChild(
            'saveButton',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Save Rate'), 'class' => 'save')
        );

        $this->addChild(
            'deleteButton',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Delete Rate'),
                'onclick' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getUrl(
                    'tax/*/delete',
                    array('rate' => $this->getRequest()->getParam('rate'))
                ) . '\')',
                'class' => 'delete'
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('backButton');
    }

    /**
     * @return string
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('resetButton');
    }

    /**
     * @return string
     */
    public function getSaveButtonHtml()
    {
        $formId = $this->getLayout()->getBlock('tax_rate_form')->getDestElementId();
        $button = $this->getChildBlock('saveButton');
        $button->setDataAttribute(
            array('mage-init' => array('button' => array('event' => 'save', 'target' => '#' . $formId)))
        );
        return $this->getChildHtml('saveButton');
    }

    /**
     * @return string|void
     */
    public function getDeleteButtonHtml()
    {
        if (intval($this->getRequest()->getParam('rate')) == 0) {
            return;
        }
        return $this->getChildHtml('deleteButton');
    }
}
