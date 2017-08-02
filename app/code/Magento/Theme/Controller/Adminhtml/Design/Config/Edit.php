<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\Design\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page as ResultPage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeValidatorInterface as ScopeValidator;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;
use Magento\Framework\App\ScopeResolverPool;

/**
 * Edit action controller
 * @since 2.1.0
 */
class Edit extends Action
{
    /**
     * @var ResultPageFactory
     * @since 2.1.0
     */
    protected $resultPageFactory;

    /**
     * @var ScopeValidator
     * @since 2.1.0
     */
    protected $scopeValidator;

    /**
     * @var ScopeResolverPool
     * @since 2.1.0
     */
    protected $scopeResolverPool;

    /**
     * @param Context $context
     * @param ResultPageFactory $resultPageFactory
     * @param ScopeValidator $scopeValidator
     * @param ScopeResolverPool $scopeResolverPool
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        ResultPageFactory $resultPageFactory,
        ScopeValidator $scopeValidator,
        ScopeResolverPool $scopeResolverPool
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeValidator = $scopeValidator;
        $this->scopeResolverPool = $scopeResolverPool;
        parent::__construct($context);
    }

    /**
     * @return ResultPage|ResultRedirect
     * @since 2.1.0
     */
    public function execute()
    {
        $scope = $this->getRequest()->getParam('scope');
        $scopeId = $this->getRequest()->getParam('scope_id');

        if (!$this->scopeValidator->isValidScope($scope, $scopeId)) {
            /** @var ResultRedirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('theme/design_config/');
            return $resultRedirect;
        }

        /** @var ResultPage $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Theme::design_config');
        $resultPage->getConfig()->getTitle()->prepend(__('%1', $this->getScopeTitle()));
        return $resultPage;
    }

    /**
     * Retrieve scope title
     *
     * @return string
     * @since 2.1.0
     */
    protected function getScopeTitle()
    {
        $scope = $this->getRequest()->getParam('scope');
        $scopeId = $this->getRequest()->getParam('scope_id');

        if ($scope != ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $scopeResolver = $this->scopeResolverPool->get($scope);
            $scopeObject = $scopeResolver->getScope($scopeId);
            return __('%1', $scopeObject->getName());
        }

        return __('Global');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     * @since 2.1.0
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Config::config_design');
    }
}
