<?php

namespace Webiators\CustomChanges\Block;


class FetchUpdate extends \Magento\Framework\View\Element\Template
{
    protected $_pageFactory;
    protected $_request;
    protected $_postFactory;

    protected $context;
    protected $resultFactory;

    public function __construct(
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,

        \Webiators\CustomChanges\Model\PostFactory $postFactory
    ) {
        // $this->_resultFactory = $resultFactory;
        $this->_pageFactory = $pageFactory;
        $this->_request = $request;
        $this->_postFactory = $postFactory;
        $this->resultFactory = $resultFactory;
        return parent::__construct($context);
    }

    public function FetchData()
    {
        $id = array("post_id" => $this->_request->getParam('id'));
        // print_r($id);die();
        $postData = $this->_postFactory->create();
        $collection = $postData->getCollection()->addFieldToFilter("post_id", $id);
        $result = $collection->getData();

        return $result;
    }
    public function UpdateData()
    {
        $post_id = array('post_id' => $this->_request->getParam('id'));
        $postdata = (array) $this->getRequest()->getPost();
        $mergeArray = array_merge($post_id,$postdata);
        // print_r($mergeArray);die();
        //  print_r($post);
        if (!empty($postdata)) {
            $model = $this->_postFactory->create();
            $model->setData($mergeArray);
            $model->save();
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('update');
            return $resultRedirect;
            // return $this->_redirect('webiatormodule/index/update');
        }
    }
}
