<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme editor container
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme;

/**
 * @api
 * @since 100.0.2
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_blockGroup = 'Magento_Theme';
        $this->_controller = 'Adminhtml_System_Design_Theme';
        $this->setId('theme_edit');

        if (is_object($this->getLayout()->getBlock('page.title'))) {
            $this->getLayout()->getBlock('page.title')->setPageTitle($this->getHeaderText());
        }

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $this->_getCurrentTheme();
        if ($theme) {
            if ($theme->isEditable()) {
                $this->buttonList->add(
                    'save_and_continue',
                    [
                        'label' => __('Save and Continue Edit'),
                        'class' => 'save',
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                            ],
                        ]
                    ],
                    1
                );
            } else {
                $this->buttonList->remove('save');
                $this->buttonList->remove('reset');
            }

            if ($theme->isDeletable()) {
                if ($theme->hasChildThemes()) {
                    $message = __('Are you sure you want to delete this theme?');
                    $onClick = sprintf(
                        "deleteConfirm('%s', '%s')",
                        $message,
                        $this->getUrl('adminhtml/*/delete', ['id' => $theme->getId()])
                    );
                    $this->buttonList->update('delete', 'onclick', $onClick);
                }
            } else {
                $this->buttonList->remove('delete');
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Prepare header for container
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $this->_getCurrentTheme();
        if ($theme->getId()) {
            $header = __('Theme: %1', $theme->getThemeTitle());
        } else {
            $header = __('New Theme');
        }
        return $header;
    }

    /**
     * Get current theme
     *
     * @return \Magento\Theme\Model\Theme
     */
    protected function _getCurrentTheme()
    {
        return $this->_coreRegistry->registry('current_theme');
    }
}
