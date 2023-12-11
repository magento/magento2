<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;

/**
 * Order Details
 */
class Details extends \Magento\Framework\View\Element\Template
{
    /**
     * @param Template\Context $context
     * @param array $data
     * @param Message|null $giftMessageHelper
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        ?GiftMessageHelper $giftMessageHelper = null
    ) {
        $data['giftMessageHelper'] = $giftMessageHelper ?? ObjectManager::getInstance()->get(GiftMessageHelper::class);
        parent::__construct($context, $data);
    }

    /**
     * @var string
     */
    protected $_template = 'Magento_Sales::order/details.phtml';
}
