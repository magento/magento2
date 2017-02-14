<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Sanbox;

use Magento\Mtf\Block\Block;

class CaseInfo extends Block
{
    private $flagGoodButton = 'button.flag-case-good';

    public function flagCaseGood()
    {
        $this->_rootElement->find($this->flagGoodButton)->click();
    }
}
