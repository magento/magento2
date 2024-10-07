<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Backend\Model\View\Result\Forward as ResultForward;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Search\Controller\Adminhtml\Term as TermController;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends TermController implements HttpGetActionInterface
{
    /**
     * @return ResultForward
     */
    public function execute()
    {
        /** @var ResultForward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        return $resultForward->forward('edit');
    }
}
