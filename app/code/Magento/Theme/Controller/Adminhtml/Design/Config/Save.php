<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\Design\Config;

use Magento\Backend\App\Action;
use Magento\Theme\Model\DesignConfigRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Theme\Model\Data\Design\ConfigFactory;
use Magento\Framework\App\ScopeValidatorInterface as ScopeValidator;

class Save extends Action
{
    /**
     * @var DesignConfigRepository
     */
    protected $designConfigRepository;

    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * @var ScopeValidator
     */
    protected $scopeValidator;

    /**
     * @param DesignConfigRepository $designConfigRepository
     * @param ConfigFactory $configFactory
     * @param ScopeValidator $scopeValidator
     * @param Context $context
     */
    public function __construct(
        DesignConfigRepository $designConfigRepository,
        ConfigFactory $configFactory,
        ScopeValidator $scopeValidator,
        Context $context
    ) {
        parent::__construct($context);
        $this->designConfigRepository = $designConfigRepository;
        $this->configFactory = $configFactory;
        $this->scopeValidator = $scopeValidator;
    }

    /**
     * Check the permission to manage themes
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Config::config_design');
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('theme/design_config/');
        $scope = $this->getRequest()->getParam('scope');
        $scopeId = (int)$this->getRequest()->getParam('scope_id');
        if (!$this->scopeValidator->isValidScope($scope, $scopeId)) {
            $this->messageManager->addError(__('Invalid scope or scope id'));
            return $resultRedirect;
        }

        $data = [
            'scope' => $scope,
            'scopeId' => $scopeId,
            'params' => $this->getRequestData(),
        ];
        $designConfigData = $this->configFactory->create($data);
        try {
            $this->designConfigRepository->save($designConfigData);
            $this->messageManager->addSuccess(__('Configuration has been saved'));
        } catch (LocalizedException $e) {
            $messages = explode("\n", $e->getMessage());
            foreach ($messages as $message) {
                $this->messageManager->addError(__($message));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
            );
        }

        return $resultRedirect;
    }

    /**
     * Extract data from request
     *
     * @return array
     */
    protected function getRequestData()
    {
        $data = array_merge(
            $this->getRequest()->getParams(),
            $this->getRequest()->getFiles()->toArray()
        );
        $data = array_filter($data, function ($param) {
            return is_array($param) && isset($param['error']) && $param['error'] > 0 ? false : true;
        });
        return $data;
    }
}
