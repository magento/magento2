<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\CreditMemos;

use Magento\Mtf\Client\Locator;

/**
 * Class Grid
 * Credit memos grid on order view page
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Base part of row locator template for getRow() method.
     *
     * @var string
     */
    protected $rowPattern = './/tr[%s]';

    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = 'tbody td[data-column="increment_id"]';

    /**
     * Css selector for credit memo ids.
     *
     * @var string
     */
    protected $creditMemoId = 'tbody td:nth-child(2)';

    /**
     * CreditMemos data grid loader Xpath locator.
     *
     * @var string
     */
    protected $loader = '//div[contains(@data-component, "sales_order_view_creditmemo_grid")]';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => 'input[name="increment_id"]',
        ],
        'status' => [
            'selector' => 'select[name="state"]',
            'input' => 'select',
        ],
        'amount_from' => [
            'selector' => '[name="base_grand_total[from]"]',
        ],
        'amount_to' => [
            'selector' => '[name="base_grand_total[to]"]',
        ],
    ];

    /**
     * Get credit memo id from grid
     *
     * @return array|string
     */
    public function getCreditMemoId()
    {
        $this->waitForElementNotVisible($this->loader, Locator::SELECTOR_XPATH);
        return $this->_rootElement->find($this->creditMemoId)->getText();
    }

    /**
     * Get credit memo ids
     *
     * @return array
     */
    public function getIds()
    {
        $result = [];
        $this->waitForElementNotVisible($this->loader, Locator::SELECTOR_XPATH);
        $creditMemoIds = $this->_rootElement->getElements($this->creditMemoId);
        foreach ($creditMemoIds as $creditMemoId) {
            $result[] = trim($creditMemoId->getText());
        }

        return $result;
    }
}
