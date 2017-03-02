<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter templates page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml;

class Template extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'template/list.phtml';

    /**
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
