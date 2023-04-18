<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Admin tax class product toolbar
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Block\Adminhtml\Rate\Toolbar;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Item;
use Magento\Backend\Block\Widget\Button\ToolbarInterface;
use Magento\Backend\Block\Widget\ContainerInterface;

/**
 * @api
 * @since 100.0.2
 */
class Add extends Template implements ContainerInterface
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Tax::toolbar/rate/add.phtml';

    /**
     * @param Context $context
     * @param ButtonList $buttonList
     * @param ToolbarInterface $toolbar
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected readonly ButtonList $buttonList,
        protected readonly ToolbarInterface $toolbar,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * {$@inheritdoc}
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        $this->buttonList->add($buttonId, $data, $level, $sortOrder, $region);
        return $this;
    }

    /**
     * {$@inheritdoc}
     */
    public function removeButton($buttonId)
    {
        $this->buttonList->remove($buttonId);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->buttonList->add(
            'add',
            [
                'label' => __('Add New Tax Rate'),
                'onclick' => 'window.location.href=\'' . $this->getUrl('tax/rate/add') . '\'',
                'class' => 'add primary add-tax-rate'
            ]
        );

        $this->toolbar->pushButtons($this, $this->buttonList);
        return parent::_prepareLayout();
    }

    /**
     * {$@inheritdoc}
     */
    public function updateButton($buttonId, $key, $data)
    {
        $this->buttonList->update($buttonId, $key, $data);
        return $this;
    }

    /**
     * {$@inheritdoc}
     */
    public function canRender(Item $item)
    {
        return !$item->isDeleted();
    }
}
