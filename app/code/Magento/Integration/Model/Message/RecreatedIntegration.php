<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model\Message;

use Magento\Framework\UrlInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\Config;
use Magento\Integration\Model\ConsolidatedConfig;
use Magento\Integration\Model\Integration;

/**
 * Class RecreatedIntegration to display message when a config-based integration needs to be reactivated
 */
class RecreatedIntegration implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var Config
     */
    protected $integrationConfig;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var IntegrationServiceInterface
     */
    protected $integrationService;

    /**
     * @var ConsolidatedConfig
     */
    protected $consolidatedConfig;

    /**
     * @param Config $integrationConfig
     * @param UrlInterface $urlBuilder
     * @param IntegrationServiceInterface $integrationService
     * @param ConsolidatedConfig $consolidatedConfig
     */
    public function __construct(
        Config $integrationConfig,
        UrlInterface $urlBuilder,
        IntegrationServiceInterface $integrationService,
        ConsolidatedConfig $consolidatedConfig
    ) {
        $this->integrationConfig = $integrationConfig;
        $this->consolidatedConfig = $consolidatedConfig;
        $this->urlBuilder = $urlBuilder;
        $this->integrationService = $integrationService;
    }

    /**
     * Check whether all indices are valid or not
     *
     * @return bool
     */
    public function isDisplayed()
    {
        foreach (array_keys($this->consolidatedConfig->getIntegrations()) as $name) {
            $integration = $this->integrationService->findByName($name);
            if ($integration->getStatus() == Integration::STATUS_RECREATED) {
                return true;
            }
        }

        return false;
    }

    //@codeCoverageIgnoreStart

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('INTEGRATION_RECREATED');
    }

    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        $url = $this->urlBuilder->getUrl('adminhtml/integration');
        return __(
            'One or more <a href="%1">integrations</a> have been reset because of a change to their xml configs.',
            $url
        );
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }

    //@codeCoverageIgnoreEnd
}
