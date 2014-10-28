<?php
/**
 * Integration edit container.
 *
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
namespace Magento\Integration\Block\Adminhtml\Integration;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Controller\Adminhtml\Integration;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registry = null;

    /** @var \Magento\Integration\Helper\Data */
    protected $_integrationHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Integration\Helper\Data $integrationHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Integration\Helper\Data $integrationHelper,
        array $data = array()
    ) {
        $this->_registry = $registry;
        $this->_integrationHelper = $integrationHelper;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Integration edit page
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_integration';
        $this->_blockGroup = 'Magento_Integration';
        parent::_construct();
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');

        if ($this->_integrationHelper->isConfigType(
            $this->_registry->registry(Integration::REGISTRY_KEY_CURRENT_INTEGRATION)
        )
        ) {
            $this->buttonList->remove('save');
        }

        if ($this->_isNewIntegration()) {
            $this->removeButton(
                'save'
            )->addButton(
                'save',
                array(
                    'id' => 'save-split-button',
                    'label' => __('Save'),
                    'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                    'button_class' => '',
                    'data_attribute' => array(
                        'mage-init' => array('button' => array('event' => 'save', 'target' => '#edit_form'))
                    ),
                    'options' => array(
                        'save_activate' => array(
                            'id' => 'activate',
                            'label' => __('Save & Activate'),
                            'data_attribute' => array(
                                'mage-init' => array(
                                    'button' => array('event' => 'saveAndActivate', 'target' => '#edit_form'),
                                    'integration' => array('gridUrl' => $this->getUrl('*/*/'))
                                )
                            )
                        )
                    )
                )
            );
        }
    }

    /**
     * Get header text for edit page.
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_isNewIntegration()) {
            return __('New Integration');
        } else {
            return __(
                "Edit Integration '%1'",
                $this->escapeHtml(
                    $this->_registry->registry(Integration::REGISTRY_KEY_CURRENT_INTEGRATION)[Info::DATA_NAME]
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * Determine whether we create new integration or editing an existing one.
     *
     * @return bool
     */
    protected function _isNewIntegration()
    {
        return !isset($this->_registry->registry(Integration::REGISTRY_KEY_CURRENT_INTEGRATION)[Info::DATA_ID]);
    }
}
