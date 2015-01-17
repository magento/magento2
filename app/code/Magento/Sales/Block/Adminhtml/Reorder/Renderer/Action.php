<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Reorder\Renderer;

/**
 * Adminhtml alert queue grid block action item renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    protected $_actions = [];

    /**
     * Sales reorder
     *
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $_salesReorder = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Sales\Helper\Reorder $salesReorder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Sales\Helper\Reorder $salesReorder,
        array $data = []
    ) {
        $this->_salesReorder = $salesReorder;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        $this->_actions = [];
        if ($this->_salesReorder->canReorder($row)) {
            $reorderAction = [
                '@' => [
                    'href' => $this->getUrl('sales/order_create/reorder', ['order_id' => $row->getId()]),
                ],
                '#' => __('Reorder'),
            ];
            $this->addToActions($reorderAction);
        }
        $this->_eventManager->dispatch(
            'adminhtml_customer_orders_add_action_renderer',
            ['renderer' => $this, 'row' => $row]
        );
        return $this->_actionsToHtml();
    }

    /**
     * Get escaped value
     *
     * @param string $value
     * @return string
     */
    protected function _getEscapedValue($value)
    {
        return addcslashes(htmlspecialchars($value), '\\\'');
    }

    /**
     * Render options array as a HTML string
     *
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions = [])
    {
        $html = [];
        $attributesObject = new \Magento\Framework\Object();

        if (empty($actions)) {
            $actions = $this->_actions;
        }

        foreach ($actions as $action) {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }
        return implode($html, '');
    }

    /**
     * Add one action array to all options data storage
     *
     * @param array $actionArray
     * @return void
     */
    public function addToActions($actionArray)
    {
        $this->_actions[] = $actionArray;
    }
}
