<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Controller\Adminhtml\Email\Template;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Rendering popup email template.
 */
class Popup extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Load the page.
     *
     * Load the page defined in view/adminhtml/layout/adminhtml_email_template_popup.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Email::template');
    }
}
