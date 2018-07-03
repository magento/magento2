<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Block\Adminhtml\Queue;

use Magento\Mtf\Client\Locator;

/**
 * Newsletter queue templates grid block.
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'newsletter_subject' => [
            'selector' => 'input[name="newsletter_subject"]',
        ],
        'start_at_from' => [
            'selector' => 'input[name="start_at[from]"]',
        ],
        'start_at_to' => [
            'selector' => 'input[name="start_at[to]"]',
        ],
    ];

    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = 'td.col-subject';

    /**
     * Locator for "Action".
     *
     * @var string
     */
    private $action = '.col-actions [class*="control-select"]';

    /**
     * Action for newsletter queue template.
     *
     * @param string $action
     * @return void
     */
    public function performAction($action)
    {
        $this->_rootElement->find($this->action, Locator::SELECTOR_CSS, 'select')->setValue($action);
    }
}
