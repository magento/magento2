<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Block\Adminhtml\Dashboard\Page;

use Magento\Mtf\Block\Block;

/**
 * Page actions block
 */
class Actions extends Block
{
    /**
     * Free Tier Link.
     *
     * @var string
     */
    protected $freeTierLink = '[data-index="analytics-service-link"]';

    /**
     * Click Free Tier link.
     *
     * @return void
     */
    public function click()
    {
        $this->_rootElement->find($this->freeTierLink)->click();
    }
}
