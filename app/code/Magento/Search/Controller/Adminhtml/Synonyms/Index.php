<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * @var \Magento\Search\Controller\Adminhtml\Synonyms\ResultPageBuilder $pageBuilder
     */
    private $pageBuilder;

    /**
     * constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Search\Controller\Adminhtml\Synonyms\ResultPageBuilder $pageBuilder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Search\Controller\Adminhtml\Synonyms\ResultPageBuilder $pageBuilder
    ) {
        $this->pageBuilder = $pageBuilder;
        parent::__construct($context);
    }

    /**
     * Synonyms grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->pageBuilder->build();
        $resultPage->getConfig()->getTitle()->prepend(__('Search Synonyms'));
        return $resultPage;
    }
}
