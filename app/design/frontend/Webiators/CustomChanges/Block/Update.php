<?php

namespace Webiators\CustomChanges\Block;



class Update extends \Magento\Framework\View\Element\Template
{
    protected $_postFactory;
    protected $_request;

    public function __construct(
		\Webiators\CustomChanges\Model\PostFactory $postFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
		)
	{
		$this->_postFactory = $postFactory;
        $this->_request = $request;
      
		return parent::__construct($context,$data);
	}
    public function getFormAction()
    {
    $post =  $this->_postFactory->create();
    $collection = $post->getCollection();
    // foreach($collection as $item){
        $data = $collection->getData();
       
    // }
    // echo"<pre>";
    // print_r($data);die();
   return $data;
    }
 
}