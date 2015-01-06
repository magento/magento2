<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Install\Test\Block;

use Mtf\Block\Form;

/**
 * Web configuration block.
 */
class WebConfiguration extends Form
{
    /**
     * 'Next' button.
     *
     * @var string
     */
    protected $next = "[ng-click*='next']";

    /**
     * 'Advanced Options' locator.
     *
     * @var string
     */
    protected $advancedOptions = "[ng-click*='advanced']";

    /**
     * Click on 'Next' button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next)->click();
    }

    /**
     * Click on 'Advanced Options' button.
     *
     * @return void
     */
    public function clickAdvancedOptions()
    {
        $this->_rootElement->find($this->advancedOptions)->click();
    }
}
