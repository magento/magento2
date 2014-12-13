<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action;

class Reorder extends \Magento\Sales\Controller\AbstractController\Reorder
{
    /**
     * @param Action\Context $context
     * @param OrderLoader $orderLoader
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        OrderLoader $orderLoader,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $orderLoader, $registry);
    }
}
