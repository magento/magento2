<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Plugin\Controller\System\Config;

use Magento\AsyncConfig\Api\AsyncConfigPublisherInterface;
use Magento\AsyncConfig\Setup\ConfigOptionsList;
use Magento\Config\Controller\Adminhtml\System\Config\Save;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Message\ManagerInterface;

class SaveAsyncConfigPlugin
{
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var AsyncConfigPublisherInterface
     */
    private $asyncConfigPublisher;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     *
     * @param DeploymentConfig $deploymentConfig
     * @param AsyncConfigPublisherInterface $asyncConfigPublisher
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        AsyncConfigPublisherInterface $asyncConfigPublisher,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->asyncConfigPublisher = $asyncConfigPublisher;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Around Config save controller
     *
     * @param Save $subject
     * @param callable $proceed
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws RuntimeException
     */
    public function aroundExecute(Save $subject, callable $proceed)
    {
        if (!$this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_ASYNC_CONFIG_SAVE)) {
            return $proceed();
        } else {
            $configData = $subject->getConfigData();
            $this->asyncConfigPublisher->saveConfigData($configData);
            $this->messageManager->addSuccessMessage(__('Configuration changes will be applied by consumer soon.'));
            $subject->_saveState($subject->getRequest()->getPost('config_state'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath(
                'adminhtml/system_config/edit',
                [
                    '_current' => ['section', 'website', 'store'],
                    '_nosid' => true
                ]
            );
        }
    }
}
