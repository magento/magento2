<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Model\Url;

use Magento\Downloadable\Api\DomainManagerInterface as DomainManager;
use Magento\Framework\Validator\Ip as IpValidator;
use Laminas\Uri\Uri as UriHandler;

/**
 * Class is responsible for checking if downloadable product link domain is allowed.
 */
class DomainValidator
{
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
     * @var DomainManager
     */
    private $domainManager;

    /**
     * @param DomainManager $domainManager
     * @param IpValidator $ipValidator
     * @param UriHandler $uriHandler
     */
    public function __construct(
        DomainManager $domainManager,
        IpValidator $ipValidator,
        UriHandler $uriHandler
    ) {
        $this->domainManager = $domainManager;
        $this->ipValidator = $ipValidator;
        $this->uriHandler = $uriHandler;
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
        $isValid = !$isIpAddress && in_array($host, $this->domainManager->getDomains());

        return $isValid;
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
}
