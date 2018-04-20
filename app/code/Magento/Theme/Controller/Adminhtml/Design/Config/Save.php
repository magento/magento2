<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Controller\Adminhtml\Design\Config;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Theme\Model\DesignConfigRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Theme\Model\Data\Design\ConfigFactory;

/**
 * Save action controller
 */
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
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Context $context
     * @param DesignConfigRepository $designConfigRepository
     * @param ConfigFactory $configFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        DesignConfigRepository $designConfigRepository,
        ConfigFactory $configFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->designConfigRepository = $designConfigRepository;
        $this->configFactory = $configFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
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
        $scope = $this->getRequest()->getParam('scope');
        $scopeId = (int)$this->getRequest()->getParam('scope_id');
        $data = $this->getRequestData();

        try {
            if (!$this->getRequest()->isPost()) {
                throw new LocalizedException(__('Wrong request.'));
            }
            $designConfigData = $this->configFactory->create($scope, $scopeId, $data);
            $this->designConfigRepository->save($designConfigData);
            $this->messageManager->addSuccessMessage(__('You saved the configuration.'));

            $this->dataPersistor->clear('theme_design_config');

            $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
            $resultRedirect->setPath('theme/design_config/');
            if ($returnToEdit) {
                $resultRedirect->setPath('theme/design_config/edit', ['scope' => $scope, 'scope_id' => $scopeId]);
            }
            return $resultRedirect;
        } catch (LocalizedException $e) {
            $messages = explode("\n", $e->getMessage());
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage(__('%1', $message));
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
            );
        }

        $this->dataPersistor->set('theme_design_config', $data);

        $resultRedirect->setPath('theme/design_config/edit', ['scope' => $scope, 'scope_id' => $scopeId]);
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
            return !(isset($param['error']) && $param['error'] > 0);
        });

        /**
         * Set null to theme id in case it's empty string,
         * in order to delete value from db config but not set empty string,
         * which may cause an error in Magento/Theme/Model/ResourceModel/Theme/Collection::getThemeByFullPath().
         */
        if (isset($data['theme_theme_id']) && $data['theme_theme_id'] === '') {
            $data['theme_theme_id'] = null;
        }
        return $data;
    }
}
