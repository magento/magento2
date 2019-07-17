<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Model\Url;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Validator\Ip as IpValidator;
use Zend\Uri\Uri as UriHandler;

/**
 * Class is responsible for checking if downloadable product link domain is allowed.
 */
class DomainValidator extends \Zend_Validate_Abstract
{
    /**
     * Invalid host message key
     */
    private const INVALID_HOST = 'invalidHost';

    /**
     * Path to the allowed domains in the deployment config
     */
    public const PARAM_DOWNLOADABLE_DOMAINS = 'downloadable_domains';

    /**
     * @var IpValidator
     */
    private $ipValidator;

    /**
     * @var UriHandler
     */
    private $uriHandler;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param IpValidator $ipValidator
     * @param UriHandler $uriHandler
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        IpValidator $ipValidator,
        UriHandler $uriHandler
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->ipValidator = $ipValidator;
        $this->uriHandler = $uriHandler;

        $this->initMessageTemplates();
    }

    /**
     * Validate url input.
     *
     * Assert parsed host of $value is contained within environment whitelist
     *
     * @param string $value
     * @return bool
     */
    public function isValid($value): bool
    {
        $host = $this->getHost($value);

        $isIpAddress = $this->ipValidator->isValid($host);
        $isValid = !$isIpAddress && in_array($host, $this->getEnvDomainWhitelist());

        if (!$isValid) {
            $this->_error(self::INVALID_HOST, $host);
        }

        return $isValid;
    }

    /**
     * Get environment whitelist
     *
     * @return array
     */
    public function getEnvDomainWhitelist(): array
    {
        return array_map('strtolower', $this->deploymentConfig->get(self::PARAM_DOWNLOADABLE_DOMAINS) ?? []);
    }

    /**
     * Extract host from url
     *
     * @param string $url
     * @return string
     */
    private function getHost($url): string
    {
        $host = $this->uriHandler->parse($url)->getHost();

        if ($host === null) {
            return '';
        }

        // ipv6 hosts are brace-delimited in url; they are removed here for subsequent validation
        return trim($host, '[] ');
    }

    /**
     * Initialize message templates with translating
     *
     * @return void
     */
    private function initMessageTemplates()
    {
        if (!$this->_messageTemplates) {
            $this->_messageTemplates = [
                self::INVALID_HOST => __('Host "%value%" is not allowed.'),
            ];
        }
    }
}
