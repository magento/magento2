<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Block\Adminhtml;

/**
 * Sitemap edit form container
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
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
     * Init container
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'sitemap_id';
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Magento_Sitemap';

        parent::_construct();

        $this->buttonList->add(
            'generate',
            [
                'label' => __('Save & Generate'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'save',
                            'target' => '#edit_form',
                            'eventData' => ['action' => ['args' => ['generate' => '1']]],
                        ],
                    ],
                ],
                'class' => 'add'
            ]
        );
    }

    /**
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('sitemap_sitemap')->getId()) {
            return __('Edit Sitemap');
        } else {
            return __('New Sitemap');
        }
    }
}
