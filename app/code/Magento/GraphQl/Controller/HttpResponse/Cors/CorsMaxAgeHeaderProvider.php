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
 * Provides value for Access-Control-Max-Age header if CORS is enabled
 */
class CorsMaxAgeHeaderProvider implements HeaderProviderInterface
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

    /**
     * Get name of header
     *
     * @return string
     */
    public function getName()
    {
        return $this->headerName;
    }

    /**
     * Check if header can be applied
     *
     * @return bool
     */
    public function canApply()
    {
        return $this->corsConfiguration->isEnabled() && $this->getValue();
    }

    /**
     * Get value for header
     *
     * @return string
     */
    public function getValue()
    {
        return $this->corsConfiguration->getMaxAge();
    }
}
