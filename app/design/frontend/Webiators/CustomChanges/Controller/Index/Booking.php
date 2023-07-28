<?php

namespace Webiators\CustomChanges\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Webiators\CustomChanges\Model\Post;
class Booking extends \Magento\Framework\App\Action\Action
{
    /**
     * Booking action
     *
     * @return void
     */
    protected $_postFactory;
    protected $_request;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Webiators\CustomChanges\Model\PostFactory $postFactory
    )
    {
        $this->_postFactory = $postFactory;
        $this->_request = $request;
        return parent::__construct($context);
    }
    public function execute()
    {

        $post = (array) $this->getRequest()->getPost();
        if (!empty($post)) {
            $model = $this->_postFactory->create();
            $model->setData($post);
            $model->save();

            $this->messageManager->addSuccessMessage('Successfully Registered');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('update');

            return $resultRedirect;
        }
         $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

}