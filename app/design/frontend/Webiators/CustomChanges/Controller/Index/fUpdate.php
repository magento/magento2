<?php
namespace Webiators\CustomChanges\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
class Update extends \Magento\Framework\App\Action\Action{

    protected $_pageFactory;
    protected $_postFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Webiators\CustomChanges\Model\PostFactory $postFactory
        )
    {
        $this->_pageFactory = $pageFactory;
        $this->_postFactory = $postFactory;
        return parent::__construct($context);
    }
    public function execute()
    {
        $post = $this->_postFactory->create();
		$collection = $post->getCollection();
        foreach($collection as $item){
            $data = $item->getData();
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $block = $page->getLayout()->getBlock('webiatormodule.update');
        $block->setData('post_content',$data);
        return $page;
        } 
     
    }
  
}