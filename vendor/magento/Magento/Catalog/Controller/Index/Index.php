<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Result\Redirect
     */
    protected $resultRedirectFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Context $context,
        Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return $this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('/');
    }
}
