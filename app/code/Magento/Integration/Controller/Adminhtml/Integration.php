<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Integration\Service\V1\OauthInterface as IntegrationOauthService;

/**
 * Controller for integrations management.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Integration extends Action
{
    /** Param Key for extracting integration id from Request */
    const PARAM_INTEGRATION_ID = 'id';

    /** Reauthorize flag is used to distinguish activation from reauthorization */
    const PARAM_REAUTHORIZE = 'reauthorize';

    const REGISTRY_KEY_CURRENT_INTEGRATION = 'current_integration';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /** @var \Psr\Log\LoggerInterface */
    protected $_logger;

    /** @var \Magento\Integration\Service\V1\IntegrationInterface */
    protected $_integrationService;

    /** @var IntegrationOauthService */
    protected $_oauthService;

    /** @var \Magento\Core\Helper\Data */
    protected $_coreHelper;

    /** @var \Magento\Integration\Helper\Data */
    protected $_integrationData;

    /** @var  \Magento\Integration\Model\Resource\Integration\Collection */
    protected $_integrationCollection;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Integration\Service\V1\IntegrationInterface $integrationService
     * @param IntegrationOauthService $oauthService
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Integration\Model\Resource\Integration\Collection $integrationCollection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Integration\Service\V1\IntegrationInterface $integrationService,
        IntegrationOauthService $oauthService,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\Integration\Helper\Data $integrationData,
        \Magento\Framework\Escaper $escaper,
        \Magento\Integration\Model\Resource\Integration\Collection $integrationCollection
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_logger = $logger;
        $this->_integrationService = $integrationService;
        $this->_oauthService = $oauthService;
        $this->_coreHelper = $coreHelper;
        $this->_integrationData = $integrationData;
        $this->escaper = $escaper;
        $this->_integrationCollection = $integrationCollection;
        parent::__construct($context);
    }

    /**
     * Check ACL.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Integration::integrations');
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
                $this->_coreHelper->jsonEncode(['_redirect' => $this->getUrl($path, $arguments)])
            );
            return $this;
        } else {
            return parent::_redirect($path, $arguments);
        }
    }
}
