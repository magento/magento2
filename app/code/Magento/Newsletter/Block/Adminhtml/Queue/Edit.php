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
namespace Magento\Newsletter\Block\Adminhtml\Queue;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Newsletter\Model\Queue as ModelQueue;

/**
 * Newsletter queue edit block
 */
class Edit extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'queue/edit.phtml';

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
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $templateId = $this->getRequest()->getParam('template_id');
        if ($templateId) {
            $this->setTemplateId($templateId);
        }
    }

    /**
     * Retrieve current Newsletter Queue Object
     *
     * @return ModelQueue
     */
    public function getQueue()
    {
        return $this->_coreRegistry->registry('current_queue');
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return AbstractBlock
     */
    protected function _beforeToHtml()
    {
        $this->setChild(
            'form',
            $this->getLayout()->createBlock('Magento\Newsletter\Block\Adminhtml\Queue\Edit\Form', 'form')
        );
        return parent::_beforeToHtml();
    }

    /**
     * Get the url for save
     *
     * @return string
     */
    public function getSaveUrl()
    {
        if ($this->getTemplateId()) {
            $params = array('template_id' => $this->getTemplateId());
        } else {
            $params = array('id' => $this->getRequest()->getParam('id'));
        }
        return $this->getUrl('*/*/save', $params);
    }

    /**
     * Prepare for the layout
     *
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Back'),
                'onclick' => "window.location.href = '" . $this->getUrl(
                    $this->getTemplateId() ? '*/template' : '*/*'
                ) . "'",
                'class' => 'action-back'
            )
        );

        $this->getToolbar()->addChild(
            'reset_button',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Reset'), 'class' => 'reset', 'onclick' => 'window.location = window.location')
        );

        $this->getToolbar()->addChild(
            'preview_button',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Preview Template'), 'onclick' => 'queueControl.preview();', 'class' => 'preview')
        );

        $this->getToolbar()->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Save Newsletter'),
                'class' => 'save primary',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'save', 'target' => '#queue_edit_form'))
                )
            )
        );

        $this->getToolbar()->addChild(
            'save_and_resume',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Save and Resume'),
                'class' => 'save',
                'data_attribute' => array(
                    'mage-init' => array(
                        'button' => array(
                            'event' => 'save',
                            'target' => '#queue_edit_form',
                            'eventData' => array('action' => array('args' => array('_resume' => 1)))
                        )
                    )
                )
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Return preview action url for form
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        return $this->getUrl('*/*/preview');
    }

    /**
     * Retrieve Preview Button HTML
     *
     * @return string
     */
    public function getPreviewButtonHtml()
    {
        return $this->getChildHtml('preview_button');
    }

    /**
     * Retrieve Save Button HTML
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Retrieve Reset Button HTML
     *
     * @return string
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Retrieve Back Button HTML
     *
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * Retrieve Resume Button HTML
     *
     * @return string
     */
    public function getResumeButtonHtml()
    {
        return $this->getChildHtml('save_and_resume');
    }

    /**
     * Getter for availability preview mode
     *
     * @return bool
     */
    public function getIsPreview()
    {
        return !in_array(
            $this->getQueue()->getQueueStatus(),
            array(ModelQueue::STATUS_NEVER, ModelQueue::STATUS_PAUSE)
        );
    }

    /**
     * Getter for single store mode check
     *
     * @return bool
     */
    protected function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * Getter for id of current store (the only one in single-store mode and current in multi-stores mode)
     *
     * @return bool
     */
    protected function getStoreId()
    {
        return $this->_storeManager->getStore(true)->getId();
    }

    /**
     * Getter for check is this newsletter the plain text.
     *
     * @return bool
     */
    public function getIsTextType()
    {
        return $this->getQueue()->isPlain();
    }

    /**
     * Getter for availability resume action
     *
     * @return bool
     */
    public function getCanResume()
    {
        return in_array($this->getQueue()->getQueueStatus(), array(ModelQueue::STATUS_PAUSE));
    }

    /**
     * Getter for header text
     *
     * @return bool
     */
    public function getHeaderText()
    {
        return $this->getIsPreview() ? __('View Newsletter') : __('Edit Newsletter');
    }
}
