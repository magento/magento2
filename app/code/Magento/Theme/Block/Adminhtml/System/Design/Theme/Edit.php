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

/**
 * Theme editor container
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme;

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
        array $data = array()
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

        if (is_object($this->getLayout()->getBlock('page-title'))) {
            $this->getLayout()->getBlock('page-title')->setPageTitle($this->getHeaderText());
        }

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $this->_getCurrentTheme();
        if ($theme) {
            if ($theme->isEditable()) {
                $this->buttonList->add(
                    'save_and_continue',
                    array(
                        'label' => __('Save and Continue Edit'),
                        'class' => 'save',
                        'data_attribute' => array(
                            'mage-init' => array(
                                'button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form')
                            )
                        )
                    ),
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
                        $this->getUrl('adminhtml/*/delete', array('id' => $theme->getId()))
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
     * @return \Magento\Core\Model\Theme
     */
    protected function _getCurrentTheme()
    {
        return $this->_coreRegistry->registry('current_theme');
    }
}
