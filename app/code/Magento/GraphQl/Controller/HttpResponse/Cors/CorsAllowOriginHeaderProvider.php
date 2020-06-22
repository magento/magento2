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
    private $headerName;

    /**
     * CORS configuration provider
     *
     * @var \Magento\GraphQl\Model\Cors\ConfigurationInterface
     */
    private $corsConfiguration;

    public function __construct(
        ConfigurationInterface $corsConfiguration,
        string $headerName
    ) {
        $this->corsConfiguration = $corsConfiguration;
        $this->headerName = $headerName;
    }

    public function getName()
    {
        return $this->headerName;
    }

    public function canApply() : bool
    {
        return $this->corsConfiguration->isEnabled() && $this->getValue();
    }

    public function getValue()
    {
        return $this->corsConfiguration->getAllowedOrigins();
    }
}
