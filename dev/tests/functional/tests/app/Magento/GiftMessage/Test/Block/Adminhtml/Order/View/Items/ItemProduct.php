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

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order\View\Items;

use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class ItemProduct
 * Item product block on OrderView page.
 */
class ItemProduct extends \Magento\Sales\Test\Block\Adminhtml\Order\Create\Items\ItemProduct
{
    /**
     * Selector for GiftOptions link.
     *
     * @var string
     */
    protected $giftOptionsLink = '[id^="gift_options_link"]';

    /**
     * Selector for order item GiftMessage form.
     *
     * @var string
     */
    protected $giftMessageForm = '//*[@role="dialog"][*[@id="gift_options_configure"]]';

    /**
     * Get GiftMessage form data.
     *
     * @param GiftMessage $giftMessage
     * @return array
     */
    public function getGiftMessageFormData(GiftMessage $giftMessage)
    {
        $giftOptionsLink = $this->_rootElement->find($this->giftOptionsLink);
        if ($giftOptionsLink->isVisible()) {
            $giftOptionsLink->click();
        }
        /** @var \Magento\GiftMessage\Test\Block\Adminhtml\Order\View\Form $giftMessageForm */
        $giftMessageForm = $this->blockFactory->create(
            'Magento\GiftMessage\Test\Block\Adminhtml\Order\View\Form',
            ['element' => $this->browser->find($this->giftMessageForm, Locator::SELECTOR_XPATH)]
        );
        $data = $giftMessageForm->getData($giftMessage);
        $giftMessageForm->closeDialog();
        return $data;
    }
}
