<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Admin tax class product toolbar
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Block\Adminhtml\Rate\Toolbar;

use Magento\Framework\View\Element\Template;

class Add extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\ContainerInterface
{
    /**
     * @var string
     */
    protected $_template = 'toolbar/rate/add.phtml';

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $buttonList;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ToolbarInterface
     */
    protected $toolbar;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @param \Magento\Backend\Block\Widget\Button\ToolbarInterface $toolbar
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList,
        \Magento\Backend\Block\Widget\Button\ToolbarInterface $toolbar,
        array $data = []
    ) {
        $this->buttonList = $buttonList;
        $this->toolbar = $toolbar;
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
    public function canRender(\Magento\Backend\Block\Widget\Button\Item $item)
    {
        return !$item->isDeleted();
    }
}
