<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

/**
 * Class \Magento\Search\Controller\Adminhtml\Synonyms\NewAction
 *
 */
class NewAction extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Search::synonyms';

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory
     */
    private $forwardFactory;

    /**
     * New Synonym Group Action constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory
    ) {
        $this->forwardFactory = $forwardFactory;
        parent::__construct($context);
    }

    /**
     * Create new synonyms group action
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $forward = $this->forwardFactory->create();
        $forward->forward('edit');
        return $forward;
    }
}
