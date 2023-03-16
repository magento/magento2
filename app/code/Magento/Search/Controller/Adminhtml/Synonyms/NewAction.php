<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Backend\Model\View\Result\Forward as ResultForward;
use Magento\Backend\Model\View\Result\ForwardFactory;

class NewAction extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * New Synonym Group Action constructor.
     *
     * @param ActionContext $context
     * @param ForwardFactory $forwardFactory
     */
    public function __construct(
        ActionContext $context,
        private readonly ForwardFactory $forwardFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Create new synonyms group action
     *
     * @return ResultForward
     */
    public function execute()
    {
        $forward = $this->forwardFactory->create();
        $forward->forward('edit');
        return $forward;
    }
}
