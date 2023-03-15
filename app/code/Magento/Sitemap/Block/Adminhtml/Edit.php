<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context as WidgetContext;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

/**
 * Sitemap edit form container
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param WidgetContext $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        WidgetContext $context,
        Registry $registry,
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
     * @return Phrase
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
