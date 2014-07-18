<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Integration\Service\V1\OauthInterface as IntegrationOauthService;
use Magento\Integration\Model\Integration as IntegrationModel;

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

    /** @var \Magento\Framework\Logger */
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
     * @param \Magento\Framework\Logger $logger
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
        \Magento\Framework\Logger $logger,
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
    protected function _redirect($path, $arguments = array())
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->representJson(
                $this->_coreHelper->jsonEncode(array('_redirect' => $this->getUrl($path, $arguments)))
            );
            return $this;
        } else {
            return parent::_redirect($path, $arguments);
        }
    }
}
