<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor;

/**
 * Editor toolbar
 *
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Container setTheme(\Magento\Framework\View\Design\ThemeInterface $theme)
 */
class Container extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Frame Url
     *
     * @var string
     */
    protected $_frameUrl;

    /**
     * Add elements in layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->addButton(
            'back_button',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl('adminhtml/*') . '\')',
                'class' => 'back'
            ]
        );

        parent::_prepareLayout();
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Store Designer');
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setFrameUrl($url)
    {
        $this->_frameUrl = $url;
        return $this;
    }

    /**
     * Retrieve frame url
     *
     * @return string
     */
    public function getFrameUrl()
    {
        return $this->_frameUrl;
    }
}
