<?php

declare(strict_types=1);

namespace Chizhov\Status\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;

class Index extends Action
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();

        if (!$request->isGet()) {
            throw new NotFoundException(__('Invalid Request.'));
        }

        /** @var \Magento\Framework\View\Result\Page $pageResult */
        $pageResult = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $pageResult->getConfig()->getTitle()->set(__('My Status'));

        return $pageResult;
    }
}
