<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Create Backup block.
 */
class CreateBackup extends Form
{
    /**
     * 'Start Update' button.
     *
     * @var string
     */
    protected $startUpdate = "[ng-click*='goToStartUpdater']";

    /**
     * Click on 'Start Update/Next' button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->startUpdate, Locator::SELECTOR_CSS)->click();
    }
}
