<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\Design\Config;

use Magento\Theme\Model\DesignConfigRepository;
use Magento\Backend\App\Action\Context;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Theme\Api\Data\DesignConfigInterfaceFactory;
use Magento\Theme\Api\Data\DesignConfigDataInterfaceFactory;
use Magento\Theme\Api\Data\DesignConfigExtensionFactory;
use Magento\Theme\Model\Design\Config\MetadataProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\Controller\Result\RedirectFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var DesignConfigRepository
     */
    protected $designConfigRepository;

    /**
     * @var DesignConfigInterfaceFactory
     */
    protected $designConfigFactory;

    /**
     * @var DesignConfigDataInterfaceFactory
     */
    protected $designConfigDataFactory;

    /**
     * @var DesignConfigExtensionFactory
     */
    protected $configExtensionFactory;

    /**
     * @var MetadataProviderInterface
     */
    protected $metadataProvider;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @param DesignConfigRepository $designConfigRepository
     * @param DesignConfigInterfaceFactory $designConfigFactory
     * @param DesignConfigDataInterfaceFactory $designConfigDataFactory
     * @param DesignConfigExtensionFactory $configExtensionFactory
     * @param MetadataProviderInterface $metadataProvider
     * @param RedirectFactory $redirectFactory
     * @param StoreManager $storeManager
     * @param Context $context
     */
    public function __construct(
        DesignConfigRepository $designConfigRepository,
        DesignConfigInterfaceFactory $designConfigFactory,
        DesignConfigDataInterfaceFactory $designConfigDataFactory,
        DesignConfigExtensionFactory $configExtensionFactory,
        MetadataProviderInterface $metadataProvider,
        RedirectFactory $redirectFactory,
        StoreManager $storeManager,
        Context $context
    ) {
        parent::__construct($context);
        $this->designConfigRepository = $designConfigRepository;
        $this->designConfigFactory = $designConfigFactory;
        $this->designConfigDataFactory = $designConfigDataFactory;
        $this->configExtensionFactory = $configExtensionFactory;
        $this->metadataProvider = $metadataProvider;
        $this->storeManager = $storeManager;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Check the permission to manage themes
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Backend::theme');
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
//            $scope = $this->getRequest()->getParam('scope');
//            $scopeId = $this->getRequest()->getParam('scope_id');
            $scope = 'default';
            $scopeId = 0;
            if (!($scope && $scopeId) && $scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                throw new LocalizedException(__('Scope and scope id is a required params'));
            }
            /** @var DesignConfigInterface $designConfigData */
            $designConfigData = $this->designConfigFactory->create();
            $designConfigData->setScope($scope);
            $designConfigData->setScopeId($scopeId);
            $this->checkSingleStoreMode($designConfigData);

            $configData = [];
            foreach ($this->metadataProvider->get() as $name => $data) {
                /** @var \Magento\Theme\Api\Data\DesignConfigDataInterface $configDataObject */
                $configDataObject = $this->designConfigDataFactory->create();
                $configDataObject->setPath($data['path']);
                $configDataObject->setFieldConfig($data);
                $configDataObject->setValue($this->getRequest()->getParam($name));
                $configData[] = $configDataObject;
            }
            /** @var \Magento\Theme\Api\Data\DesignConfigExtension $designConfigExtension */
            $designConfigExtension = $this->configExtensionFactory->create();
            $designConfigExtension->setDesignConfigData($configData);
            $designConfigData->setExtensionAttributes($designConfigExtension);

            $this->designConfigRepository->save($designConfigData);

            $this->messageManager->addSuccess(__('Configuration was saved'));
        } catch (LocalizedException $e) {
            $messages = explode("\n", $e->getMessage());
            foreach ($messages as $message) {
                $this->messageManager->addError($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(
            'theme/design_config/edit',
            ['scope' => '', 'scopeId' => '']
        );
        return $resultRedirect;
    }

    /**
     * @param DesignConfigInterface $designConfigData
     * @return void
     */
    protected function checkSingleStoreMode(DesignConfigInterface $designConfigData)
    {
        $isSingleStoreMode = $this->storeManager->isSingleStoreMode();
        if (!$isSingleStoreMode) {
            return;
        }
        $websites = $this->storeManager->getWebsites();
        $singleStoreWebsite = array_shift($websites);
        $designConfigData->setScope('websites');
        $designConfigData->setScopeId($singleStoreWebsite->getId());
    }
}
