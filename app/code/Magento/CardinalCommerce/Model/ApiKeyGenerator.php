<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model;

use Magento\Framework\Jwt\Data\Jwk;
use Magento\Framework\Jwt\KeyGeneratorInterface;
use Magento\Framework\Jwt\KeyGenerator\SecretKeyFactory;

/**
 * Generates JWK from API key.
 */
class ApiKeyGenerator implements KeyGeneratorInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var SecretKeyFactory
     */
    private $keyFactory;

    /**
     * @param Config $config
     * @param SecretKeyFactory $keyFactory
     */
    public function __construct(Config $config, SecretKeyFactory $keyFactory)
    {
        $this->config = $config;
        $this->keyFactory = $keyFactory;
    }

    /**
     * @inheritdoc
     */
    public function create(): Jwk
    {
        $key = $this->config->getApiKey();
        return $this->keyFactory->create($key);
    }
}
