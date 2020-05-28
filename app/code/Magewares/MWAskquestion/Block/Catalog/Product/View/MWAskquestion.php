<?php

namespace Magewares\MWAskquestion\Block\Catalog\Product\View;

class MWAskquestion extends \Magento\Framework\View\Element\Template
{
	protected $scopeConfig;
	protected $registry;
	
	public function __construct(
	\Magento\Framework\View\Element\Template\Context $context,
	\Magento\Framework\Registry $registry
	){
		$this->scopeConfig = $context->getScopeConfig();
		$this->registry  = $registry;
		parent::__construct($context);
	}
	
	public function isModuleEnabled()
	{
		$moduleEnabled=$this->scopeConfig->getValue('mwaskquestion/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $moduleEnabled;
	}
	
	public function getProductName()
	{
		return $this->registry->registry('current_product')->getName();
	}
	
	public function getProductId()
	{
		return $this->registry->registry('current_product')->getId();
	}
	
}
