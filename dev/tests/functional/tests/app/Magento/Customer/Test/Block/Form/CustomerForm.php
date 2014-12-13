<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Block\Form;

use Mtf\Block\Form;

/**
 * Class CustomerForm
 * Customer account edit form
 */
class CustomerForm extends Form
{
    /**
     * Save button button css selector
     *
     * @var string
     */
    protected $saveButton = '[type="submit"]';

    /**
     * Locator for customer attribute on Edit Account Information page
     *
     * @var string
     */
    protected $customerAttribute = "[name='%s[]']";

    /**
     * Click on save button
     *
     * @return void
     */
    public function submit()
    {
        $this->_rootElement->find($this->saveButton)->click();
    }
}
