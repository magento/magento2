<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Block\Extension;

use Magento\Mtf\Client\Locator;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Class UpdateGrid
 *
 * Grid with extension updates.
 */
class UpdateGrid extends AbstractGrid
{
    /**
     * 'Update All' button.
     *
     * @var string
     */
    protected $updateAllButton = "[ng-click*='updateAll']";

    /**
     * Grid that contains the list of extensions.
     *
     * @var string
     */
    protected $dataGrid = '#updateExtensionGrid';

    /**
     * Click to update all button.
     *
     * @return void
     */
    public function clickUpdateAllButton()
    {
        $this->_rootElement->find($this->updateAllButton, Locator::SELECTOR_CSS)->click();
    }
}
