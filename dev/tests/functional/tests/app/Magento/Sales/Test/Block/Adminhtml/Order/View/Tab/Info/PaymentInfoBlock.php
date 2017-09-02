<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info;

use Magento\Mtf\Block\Block;

/**
 * Order payment information block.
 */
class PaymentInfoBlock extends Block
{
    /**
     * Payment info row selector.
     *
     * @var string
     */
    private $info = 'tr';

    /**
     * Get payment information block data.
     *
     * @return array
     */
    public function getData()
    {
        $result = [];
        $elements = $this->_rootElement->getElements($this->info);
        foreach ($elements as $row) {
            $key = rtrim($row->find('th')->getText(), ':');
            $value = $row->find('td')->getText();
            $result[$key] = $value;
        }

        return $result;
    }
}
