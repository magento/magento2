<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Url\Validator;
use Magento\Framework\App\ObjectManager;

/**
 * Controller for integrations management.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Integration extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Integration::integrations';

    /** Param Key for extracting integration id from Request */
    public const PARAM_INTEGRATION_ID = 'id';

    /** Reauthorize flag is used to distinguish activation from reauthorization */
    public const PARAM_REAUTHORIZE = 'reauthorize';

    public const REGISTRY_KEY_CURRENT_INTEGRATION = 'current_integration';

    /** Saved API form data session key */
    public const REGISTRY_KEY_CURRENT_RESOURCE = 'current_resource';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    protected $_integrationService;

    /**
     * @var \Magento\Integration\Api\OauthServiceInterface
     */
    protected $_oauthService;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Integration\Helper\Data
     */
    protected $_integrationData;

    /**
     * @var \Magento\Integration\Model\ResourceModel\Integration\Collection
     */
    protected $_integrationCollection;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var Validator
     */
    protected $urlValidator;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param \Magento\Integration\Api\OauthServiceInterface $oauthService
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Integration\Model\ResourceModel\Integration\Collection $integrationCollection
     * @param Validator|null $urlValidator
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        \Magento\Integration\Api\OauthServiceInterface $oauthService,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Integration\Helper\Data $integrationData,
        \Magento\Framework\Escaper $escaper,
        \Magento\Integration\Model\ResourceModel\Integration\Collection $integrationCollection,
        Validator $urlValidator = null
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_logger = $logger;
        $this->_integrationService = $integrationService;
        $this->_oauthService = $oauthService;
        $this->jsonHelper = $jsonHelper;
        $this->_integrationData = $integrationData;
        $this->escaper = $escaper;
        $this->_integrationCollection = $integrationCollection;
        $this->urlValidator = $urlValidator ?: ObjectManager::getInstance()->get(Validator::class);
        parent::__construct($context);
    }

    /**
     * Don't actually redirect if we've got AJAX request - return redirect URL instead.
     *
     * @param string $path
     * @param array $arguments
     * @return $this|\Magento\Backend\App\AbstractAction
     */
    protected function _redirect($path, $arguments = [])
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode(['_redirect' => $this->getUrl($path, $arguments)])
            );
            return $this;
        } else {
            return parent::_redirect($path, $arguments);
        }
    }

    /**
     * Restore saved form resources
     *
     * @return void
     */
    protected function restoreResourceAndSaveToRegistry()
    {
        $restoredFormData = $this->_getSession()->getIntegrationData();
        if ($restoredFormData) {
            $resource = isset($restoredFormData['resource']) ? $restoredFormData['resource'] : [];
            $this->_registry->register(
                self::REGISTRY_KEY_CURRENT_RESOURCE,
                ['all_resources' => $restoredFormData['all_resources'], 'resource' => $resource]
            );
        }
    }
}
