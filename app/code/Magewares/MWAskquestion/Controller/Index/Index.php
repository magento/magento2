<?php 
namespace Magewares\MWAskquestion\Controller\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action {
	
    protected $scopeConfig;
	protected $resultJsonFactory;
	protected $resultpagefactory;
	protected $templateInterface;
	protected $inlineTranslation;
	protected $transportBuilder;
	protected $storeManager;
	
     public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		PageFactory $resultpagefactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\Mail\TemplateInterface $templateInterface,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Store\Model\StoreManagerInterface $storeManager
    ){
		parent::__construct($context);
		$this->scopeConfig = $scopeConfig;
		$this->resultpagefactory = $resultpagefactory;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->templateInterface =  $templateInterface;
		$this->_inlineTranslation = $inlineTranslation;
		$this->_storeManager = $storeManager;
		$this->_transportBuilder = $transportBuilder;
    }
	
    public function execute(){
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	
     if($this->getRequest()->getParams()){
		 
		   $contactName=$this->getRequest()->getParam('name');
		   $email = $this->getRequest()->getParam('email');
		   $pid=$this->getRequest()->getParam('pid');
		   $pname=$this->getRequest()->getParam('pname');
		   $question=$this->getRequest()->getParam('comment');
		   $receiverEmail=$this->scopeConfig->getValue('mwaskquestion/general/recipient_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		   try {
			
			$error = false;
			
			if (!\Zend_Validate::is(trim($contactName), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($question), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($email), 'EmailAddress')) {
                $error = true;
            }
            if ($error) {
                throw new \Exception();
            }

			if($receiverEmail){ 
				/* Receiver Detail  */
				$receiverInfo = array(
					'name' => 'Support',
					'email' => $receiverEmail,
				);
			}
		 
			/* Sender Detail  */
			$senderInfo = array(
				'name' =>  $contactName,
				'email' => $email
			);

		   $templateVariable = array(
			   'name' => $contactName,
			   'email'  => $email,
			   'question' => $question
		   );
		   
            $this->sendMail($templateVariable,$senderInfo,$receiverInfo);
			
			$this->messageManager->addSuccess(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
        } catch (\Exception $e) {
			$this->logger = $objectManager->create('\Psr\Log\LoggerInterface');
            $this->logger->critical($e->getMessage());
			 $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.')
            );
        }
		//$resultFactory = $objectManager->get('\Magento\Framework\View\Result\PageFactory');
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;		
	   }
    }
    public function sendMail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $this->_inlineTranslation->suspend();     
      $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);  
	  try{	  
      $transport = $this->_transportBuilder->getTransport();
		$transport->sendMessage(); 
	  }catch(\Exception $e)	 {
		  $this->logger = $objectManager->create('\Psr\Log\LoggerInterface');
          $this->logger->critical($e->getMessage());
	  } 
      $this->_inlineTranslation->resume();
	}
    public function generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
	 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	 $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
	 $postObject = new \Magento\Framework\DataObject();
     $postObject->setData($emailTemplateVariables);
	 $templateText = $this->templateInterface->setId('ask_question')->setVars($emailTemplateVariables)->processTemplate();
	 try{
     $transport = $this->_transportBuilder
                ->setTemplateIdentifier('ask_question')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo);
    }catch(\Exception $e)	 {
		  $this->logger = $objectManager->create('\Psr\Log\LoggerInterface');
          $this->logger->critical($e->getMessage());
	  } 
	}
}