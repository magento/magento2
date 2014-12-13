<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Backend\Test\Block\System\Store;

use Magento\Backend\Test\Block\PageActions;

/**
 * Class FormPageFooterActions
 * Form page actions footer block
 */
class FormPageFooterActions extends PageActions
{
    /**
     * "Delete" button
     *
     * @var string
     */
    protected $deleteButton = '#delete';

    /**
     * Click on "Delete" button without acceptAlert
     *
     * @return void
     */
    public function delete()
    {
        $this->_rootElement->find($this->deleteButton)->click();
    }
}
