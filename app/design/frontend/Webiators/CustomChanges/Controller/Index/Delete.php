<?php
namespace Webiators\CustomChanges\Controller\Index;

class Delete extends \Magento\Framework\App\Action\Action
{
protected $_pageFactory;
protected $_request;
protected $_postFactory;

public function __construct(
\Magento\Framework\App\Action\Context $context,
\Magento\Framework\View\Result\PageFactory $pageFactory,
\Magento\Framework\App\Request\Http $request,
\Webiators\CustomChanges\Model\PostFactory $postFactory
){
$this->_pageFactory = $pageFactory;
$this->_request = $request;
$this->_postFactory = $postFactory;
return parent::__construct($context);
}

public function execute()
{
$id = $this->_request->getParam('id');
// print_r($id);die();
$postData = $this->_postFactory->create();
$result = $postData->setId($id);
$result = $result->delete();
// $this->messageManager->addSuccessMessage('Successfully Deleted');
return $this->_redirect('webiatormodule/index/update');
}
}