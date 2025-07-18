<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Jwt\Jwk;
use Magento\Framework\Jwt\JwkFactory;
use Magento\Framework\Jwt\JwkSet;

/**
 * Creates JWT settings instances using Magento secret.
 */
class SecretBasedJwksFactory
{
    /**
     * @var string[]
     */
    private $keys;

    /**
     * @var JwkFactory
     */
    private $jwkFactory;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param JwkFactory $jwkFactory
     */
    public function __construct(DeploymentConfig $deploymentConfig, JwkFactory $jwkFactory)
    {
        $this->keys = preg_split('/\s+/s', trim((string)$deploymentConfig->get('crypt/key')));
        $this->keys = [end($this->keys)];
        //Making sure keys are large enough.
        foreach ($this->keys as &$key) {
            $key = str_pad($key, 2048, '&', STR_PAD_BOTH);
        }
        $this->jwkFactory = $jwkFactory;
    }

    /**
     * Create JWKs for given algorithm.
     *
     * @param string $algorithm
     * @return Jwk[]
     * @throws \InvalidArgumentException When algorithm is not recognized.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function createFor(string $algorithm): array
    {
        switch ($algorithm) {
            case Jwk::ALGORITHM_HS256:
                return array_map(
                    function (string $key): Jwk {
                        static $i = 0;

                        return $this->jwkFactory->createHs256($key, (string) ++$i);
                    },
                    $this->keys
                );
            case Jwk::ALGORITHM_HS384:
                return array_map(
                    function (string $key): Jwk {
                        static $i = 0;

                        return $this->jwkFactory->createHs384($key, (string) ++$i);
                    },
                    $this->keys
                );
            case Jwk::ALGORITHM_HS512:
                return array_map(
                    function (string $key): Jwk {
                        static $i = 0;

                        return $this->jwkFactory->createHs512($key, (string) ++$i);
                    },
                    $this->keys
                );
            case Jwk::ALGORITHM_A128KW:
                return array_map(
                    function (string $key): Jwk {
                        static $i = 0;

                        return $this->jwkFactory->createA128KW($key, (string) ++$i);
                    },
                    $this->keys
                );
            case Jwk::ALGORITHM_A192KW:
                return array_map(
                    function (string $key): Jwk {
                        static $i = 0;

                        return $this->jwkFactory->createA192KW($key, (string) ++$i);
                    },
                    $this->keys
                );
            case Jwk::ALGORITHM_A256KW:
                return array_map(
                    function (string $key): Jwk {
                        static $i = 0;

                        return $this->jwkFactory->createA256KW($key, (string) ++$i);
                    },
                    $this->keys
                );
            case Jwk::ALGORITHM_A128GCMKW:
                return array_map(
                    function (string $key): Jwk {
                        static $i = 0;

                        return $this->jwkFactory->createA128Gcmkw($key, (string) ++$i);
                    },
                    $this->keys
                );
            case Jwk::ALGORITHM_A192GCMKW:
                return array_map(
                    function (string $key): Jwk {
                        static $i = 0;

                        return $this->jwkFactory->createA192Gcmkw($key, (string) ++$i);
                    },
                    $this->keys
                );
            case Jwk::ALGORITHM_A256GCMKW:
                return array_map(
                    function (string $key): Jwk {
                        static $i = 0;

                        return $this->jwkFactory->createA256Gcmkw($key, (string) ++$i);
                    },
                    $this->keys
                );
            default:
                throw new \InvalidArgumentException('Unknown algorithm "' . $algorithm . '"');
        }
    }
}
