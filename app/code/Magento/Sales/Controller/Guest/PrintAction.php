<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action\Context;

class PrintAction extends \Magento\Sales\Controller\AbstractController\PrintAction
{
    /**
     * @param Context $context
     * @param OrderLoader $orderLoader
     */
    public function __construct(Context $context, OrderLoader $orderLoader)
    {
        parent::__construct($context, $orderLoader);
    }
}
