<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Newsletter templates page content block
 */
namespace Magento\Newsletter\Block\Adminhtml;

class Template extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Newsletter::template/list.phtml';

    /**
     * Prepare the layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'add_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Add New Template'),
                'onclick' => "window.location='" . $this->getCreateUrl() . "'",
                'class' => 'add primary add-template'
            ]
        );

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                \Magento\Newsletter\Block\Adminhtml\Template\Grid::class,
                'newsletter.template.grid'
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * Get the url for create
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Newsletter Templates');
    }
}
