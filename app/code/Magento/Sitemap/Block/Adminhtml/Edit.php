<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Block\Adminhtml;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Helper\PostHelper;

/**
 * Sitemap edit form container
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param PostHelper|null $postHelper
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [],
        PostHelper $postHelper = null
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->postHelper = $postHelper ?: ObjectManager::getInstance()->create(PostHelper::class);
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
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->buttonList->update(
            'delete',
            '',
            [
                'label' => __('Delete'),
                'class' => 'delete',
                'onclick' => 'deleteConfirm(\'Are you sure you want to do this?\', \'' .
                    $this->getDeleteUrl() . '\','.$this->postHelper->getPostData($this->getDeleteUrl()).')',
                'id' => 'delete',
                'button_key' => 'delete_button',
                'region' => 'toolbar',
                'level' => 0,
                'sort_order' => 10
            ]
        );

        parent::_prepareLayout();
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
