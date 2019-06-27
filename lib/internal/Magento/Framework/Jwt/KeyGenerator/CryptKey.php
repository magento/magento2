<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt\KeyGenerator;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Jwt\Data\Jwk;
use Magento\Framework\Jwt\KeyGeneratorInterface;
use Magento\Framework\Jwt\AlgorithmFactory;

/**
 * Generates JWK based on deployment crypt key.
 */
class CryptKey implements KeyGeneratorInterface
{
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var SecretKeyFactory
     */
    private $keyFactory;

    /**
     * @param SecretKeyFactory $keyFactory
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(SecretKeyFactory $keyFactory, DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
        $this->keyFactory = $keyFactory;
    }

    /**
     * @inheritdoc
     */
    public function create(): Jwk
    {
        $secret = (string) $this->deploymentConfig->get('crypt/key');
        return $this->keyFactory->create($secret);
    }
}
