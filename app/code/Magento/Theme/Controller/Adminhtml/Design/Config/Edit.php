<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\Design\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page as ResultPage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;

class Edit extends Action
{
    /**
     * @var ResultPageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ScopeResolverPool
     */
    protected $scopeResolverPool;

    /**
     * @param Context $context
     * @param ResultPageFactory $resultPageFactory
     * @param ScopeResolverPool $scopeResolverPool
     */
    public function __construct(
        Context $context,
        ResultPageFactory $resultPageFactory,
        ScopeResolverPool $scopeResolverPool
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeResolverPool = $scopeResolverPool;
        parent::__construct($context);
    }

    /**
     * @return ResultPage
     */
    public function execute()
    {
        if (!$this->isValidScope()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('theme/design_config/edit', ['scope' => 'default']);
            return $resultRedirect;
        }

        /** @var ResultPage $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Theme::design_config');
        $resultPage->getConfig()->getTitle()->prepend(__($this->getScopeTitle()));
        return $resultPage;
    }

    /**
     * Validate the requested scope
     *
     * @return bool
     */
    protected function isValidScope()
    {
        $scope = $this->getRequest()->getParam('scope');
        $scopeId = $this->getRequest()->getParam('scope_id');

        if ($scope == ScopeConfigInterface::SCOPE_TYPE_DEFAULT && !$scopeId) {
            return true;
        }

        try {
            $scopeResolver = $this->scopeResolverPool->get($scope);
            if (!$scopeResolver->getScope($scopeId)->getId()) {
                return false;
            }
        } catch (\InvalidArgumentException $e) {
            return false;
        } catch (NoSuchEntityException $e) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve scope title
     *
     * @return string
     */
    protected function getScopeTitle()
    {
        $scope = $this->getRequest()->getParam('scope');
        $scopeId = $this->getRequest()->getParam('scope_id');

        if ($scope != ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $scopeResolver = $this->scopeResolverPool->get($scope);
            $scopeObject = $scopeResolver->getScope($scopeId);
            return sprintf('%s: %s', ucfirst($scopeObject->getScopeType()), $scopeObject->getName());
        }

        return 'Default';
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Config::config_design');
    }
}
