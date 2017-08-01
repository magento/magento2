<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter templates page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml;

/**
 * Class \Magento\Newsletter\Block\Adminhtml\Template
 *
 * @since 2.0.0
 */
class Template extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'template/list.phtml';

    /**
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Newsletter Templates');
    }
}
