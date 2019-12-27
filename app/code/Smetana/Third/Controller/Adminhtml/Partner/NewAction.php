<?php
namespace Smetana\Third\Controller\Adminhtml\Partner;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result;

/**
 * New Partner class
 *
 * @package Smetana\Third\Controller\Adminhtml\Partner
 */
class NewAction extends Action
{
    /**
     * Forward factory instance
     *
     * @var Result\ForwardFactory
     */
    private $forwardFactory;

    /**
     * @param Action\Context $context
     * @param Result\ForwardFactory $forwardFactory
     */
    public function __construct(
        Action\Context $context,
        Result\ForwardFactory $forwardFactory
    ) {
        $this->forwardFactory = $forwardFactory;
        parent::__construct($context);
    }

    /**
     * Execute new action
     */
    public function execute()
    {
        /** @var Result\Forward $resultForward */
        $resultForward = $this->forwardFactory->create();
        return $resultForward->forward('edit');
    }
}
