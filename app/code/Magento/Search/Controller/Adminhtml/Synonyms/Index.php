<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Backend\Model\View\Result\Page as ResultPage;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * constructor.
     *
     * @param ActionContext $context
     * @param ResultPageBuilder $pageBuilder
     */
    public function __construct(
        ActionContext $context,
        private readonly ResultPageBuilder $pageBuilder
    ) {
        parent::__construct($context);
    }

    /**
     * Synonyms grid
     *
     * @return ResultPage
     */
    public function execute()
    {
        $resultPage = $this->pageBuilder->build();
        $resultPage->getConfig()->getTitle()->prepend(__('Search Synonyms'));
        return $resultPage;
    }
}
