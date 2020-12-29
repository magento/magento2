<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller\Cors\HttpResponseHeaderProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;
use Magento\GraphQl\Model\Cors\ConfigurationProviderInterface;
use Magento\GraphQl\Model\Cors\Validator\RequestValidatorInterface;

/**
 * Provides value for Access-Control-Allow-Origin header if CORS is enabled
 */
class AllowOriginHeaderProvider implements HeaderProviderInterface
{
    /**
     * @var string
     */
    private $headerName;

    /**
     * CORS configuration provider
     *
     * @var ConfigurationProviderInterface
     */
    private $corsConfiguration;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RequestValidatorInterface
     */
    private $requestValidator;

    /**
     * @param ConfigurationProviderInterface $corsConfiguration
     * @param RequestInterface $request
     * @param RequestValidatorInterface $requestValidator
     * @param string $headerName
     */
    public function __construct(
        ConfigurationProviderInterface $corsConfiguration,
        RequestInterface $request,
        RequestValidatorInterface $requestValidator,
        string $headerName
    ) {
        $this->corsConfiguration = $corsConfiguration;
        $this->headerName = $headerName;
        $this->request = $request;
        $this->requestValidator = $requestValidator;
    }

    /**
     * Get name of header
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->headerName;
    }

    /**
     * Check if header can be applied
     *
     * @return bool
     */
    public function canApply(): bool
    {
        return $this->requestValidator->isOriginAllowed() && $this->getValue();
    }

    public function getValue(): string
    {
        return $this->isAllOriginsAllowed() ? '*' : $this->request->getHeader('Origin');
    }

    /**
     * if '*' is present, allow all origins
     *
     * @return bool
     */
    private function isAllOriginsAllowed(): bool
    {
        return in_array('*', $this->corsConfiguration->getAllowedOrigins());
    }
}
