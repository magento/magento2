<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Sidebar block.
 */
abstract class Sidebar extends Block
{
    /**
     * 'Add to order' checkbox.
     *
     * @var string
     */
    protected $addToOrder = '//tr[td[.="%s"]]//input[contains(@name,"add")]';

    /**
     * 'Add to order' checkbox.
     *
     * @var string
     */
    protected $addToOrderProductName = '//tr/td[.="%s"]';

    /**
     * Add product to order by name.
     *
     * @param string|array $names
     * @return void
     */
    public function addToOrderByName($names)
    {
        $names = is_array($names) ? $names : [$names];
        foreach ($names as $name) {
            $this->_rootElement->find(sprintf($this->addToOrderProductName, $name), Locator::SELECTOR_XPATH)->click();
            $this->_rootElement->click();
            $this->_rootElement->find(sprintf($this->addToOrder, $name), Locator::SELECTOR_XPATH, 'checkbox')
                ->setValue('Yes');
        }
    }
}
