<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller\HttpResponse\Cors;

use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;
use Magento\GraphQl\Model\Cors\ConfigurationInterface;

/**
 * Provides value for Access-Control-Allow-Origin header if CORS is enabled
 */
class CorsAllowOriginHeaderProvider implements HeaderProviderInterface
{
    /**
     * @var string
     */
    private $headerName;

    /**
     * CORS configuration provider
     *
     * @var \Magento\GraphQl\Model\Cors\ConfigurationInterface
     */
    private $corsConfiguration;

    /**
     * @param ConfigurationInterface $corsConfiguration
     * @param string $headerName
     */
    public function __construct(
        ConfigurationInterface $corsConfiguration,
        string $headerName
    ) {
        $this->corsConfiguration = $corsConfiguration;
        $this->headerName = $headerName;
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
        return $this->corsConfiguration->isEnabled() && $this->getValue();
    }

    /**
     * Get value for header
     *
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->corsConfiguration->getAllowedOrigins();
    }
}
