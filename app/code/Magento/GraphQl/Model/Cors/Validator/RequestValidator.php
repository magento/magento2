<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Cors\Validator;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\GraphQl\Model\Cors\ConfigurationProviderInterface;

/**
 * Validates the request
 */
class RequestValidator implements RequestValidatorInterface
{

    /**
     * @var ConfigurationProviderInterface
     */
    private $configuration;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * CorsValidator constructor.
     *
     * @param ConfigurationProviderInterface $configuration
     * @param RequestInterface $request
     */
    public function __construct(
        ConfigurationProviderInterface $configuration,
        RequestInterface $request
    ) {
        $this->configuration = $configuration;
        $this->request = $request;
    }

    /**
     * Determines whether the requested origin is present in configuration
     *
     * @return bool
     */
    private function isOriginExistsInConfiguration(): bool
    {
        return in_array($this->request->getHeader('Origin'), $this->configuration->getAllowedOrigins());
    }

    /**
     * Determines whether all origins should be allowed
     *
     * @return bool
     */
    private function isAllOriginsAllowed(): bool
    {
        return in_array('*', $this->configuration->getAllowedOrigins());
    }

    /**
     * Determines whether the request is valid and applies CORS headers
     * @return bool
     */
    public function isOriginAllowed(): bool
    {
        if ($this->request instanceof HttpRequest) {

            if (!$this->originHeaderExists()) {
                return false;
            }

            return $this->isAllOriginsAllowed() || $this->isOriginExistsInConfiguration();
        }

        return false;
    }

    /**
     * Determines whether an origin header exists
     * @return bool
     */
    private function originHeaderExists(): bool
    {
        try {
            return $this->request->getHeader('Origin') ? true : false;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
