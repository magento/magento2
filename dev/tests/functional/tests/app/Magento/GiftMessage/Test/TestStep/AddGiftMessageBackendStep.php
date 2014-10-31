<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GiftMessage\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Mtf\TestStep\TestStepInterface;

/**
 * Class AddGiftMessageBackendStep
 * Add gift message to order or item on backend
 */
class AddGiftMessageBackendStep implements TestStepInterface
{
    /**
     * Sales order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Gift message fixture.
     *
     * @var GiftMessage
     */
    protected $giftMessage;

    /**
     * Array with products.
     *
     * @var array
     */
    protected $products;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param GiftMessage $giftMessage
     * @param array $products
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        GiftMessage $giftMessage,
        array $products = []
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->giftMessage = $giftMessage;
        $this->products = $products;
    }

    /**
     * Add gift message to backend order.
     *
     * @return array
     */
    public function run()
    {
        if ($this->giftMessage->getAllowGiftMessagesForOrder()) {
            $this->orderCreateIndex->getGiftMessageForOrderBlock()->fill($this->giftMessage);
        }
        if ($this->giftMessage->getAllowGiftOptionsForItems()) {
            $this->orderCreateIndex->getCreateGiftMessageBlock()
                ->fillGiftMessageForItems($this->products, $this->giftMessage);
        }

        return ['giftMessage' => $this->giftMessage];
    }
}
