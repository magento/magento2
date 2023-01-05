<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Controller\Adminhtml\System\Config;

use Magento\AsyncConfig\Api\AsyncConfigPublisherInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;

class Save extends \Magento\Config\Controller\Adminhtml\System\Config\Save
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
     * @var const
     */
    public const ASYNC_CONFIG_OPTION_PATH = 'config/async';

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \Magento\Config\Model\Config\Factory $configFactory
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param DeploymentConfig|null $deploymentConfig
     * @param AsyncConfigPublisherInterface|null $asyncConfigPublisher
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\Stdlib\StringUtils $string,
        DeploymentConfig $deploymentConfig = null,
        AsyncConfigPublisherInterface $asyncConfigPublisher = null
    ) {
        parent::__construct(
            $context,
            $configStructure,
            $sectionChecker,
            $configFactory,
            $cache,
            $string
        );
        $this->deploymentConfig = $deploymentConfig
            ?? ObjectManager::getInstance()->get(DeploymentConfig::class);
        $this->asyncConfigPublisher = $asyncConfigPublisher
            ?? ObjectManager::getInstance()->get(AsyncConfigPublisherInterface::class);
    }

    /**
     *
     * Execute Save action
     * @throws LocalizedException
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function execute()
    {
        if (!$this->deploymentConfig->get(self::ASYNC_CONFIG_OPTION_PATH)) {
            return parent::execute();
        } else {
            $configData = $this->getConfigData();
            $this->asyncConfigPublisher->saveConfigData($configData);
            $this->messageManager->addSuccess(__('Configuration changes will be applied by consumer soon.'));
            $this->_saveState($this->getRequest()->getPost('config_state'));
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
