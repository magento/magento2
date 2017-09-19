<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Controller\Button;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\OneTouchOrdering\Model\OneTouchOrdering;

class Available extends \Magento\Framework\App\Action\Action
{
    /**
     * @var OneTouchOrdering
     */
    private $oneTouchOrdering;

    public function __construct(
        Context $context,
        OneTouchOrdering $oneTouchOrdering
    ) {
        parent::__construct($context);
        $this->oneTouchOrdering = $oneTouchOrdering;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData([
            'available' => $this->oneTouchOrdering->isOneTouchOrderingAvailable()
        ]);

        return $result;
    }
}
