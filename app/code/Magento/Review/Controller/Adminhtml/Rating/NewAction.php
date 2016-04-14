<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Rating;

use Magento\Review\Controller\Adminhtml\Rating as RatingController;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends RatingController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('edit');
        return $resultForward;
    }
}
