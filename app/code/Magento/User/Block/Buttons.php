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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\User\Block;

class Buttons extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'backButton',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Back'),
                'onclick' => 'window.location.href=\'' . $this->getUrl('*/*/') . '\'',
                'class' => 'back'
            )
        );

        $this->getToolbar()->addChild(
            'resetButton',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Reset'), 'onclick' => 'window.location.reload()', 'class' => 'reset')
        );

        if (intval($this->getRequest()->getParam('rid'))) {
            $this->getToolbar()->addChild(
                'deleteButton',
                'Magento\Backend\Block\Widget\Button',
                array(
                    'label' => __('Delete Role'),
                    'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getUrl(
                        '*/*/delete',
                        array('rid' => $this->getRequest()->getParam('rid'))
                    ) . '\')',
                    'class' => 'delete'
                )
            );
        }

        $this->getToolbar()->addChild(
            'saveButton',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Save Role'),
                'class' => 'save primary save-role',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'save', 'target' => '#role-edit-form'))
                )
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
        return $this->getChildHtml('saveButton');
    }

    /**
     * @return string|void
     */
    public function getDeleteButtonHtml()
    {
        if (intval($this->getRequest()->getParam('rid')) == 0) {
            return;
        }
        return $this->getChildHtml('deleteButton');
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->_coreRegistry->registry('user_data');
    }
}
