<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Jwt\Jwk;

/**
 * JWT Algorithm options
 */
class JwtAlgorithmSource implements OptionSourceInterface
{
    public const ALG_TYPE_JWS = 0;

    public const ALG_TYPE_JWE = 1;

    private const ALG_TYPE_NAME = [
        self::ALG_TYPE_JWS => 'JWS',
        self::ALG_TYPE_JWE => 'JWE'
    ];

    private const ALGS = [
        Jwk::ALGORITHM_HS256 => self::ALG_TYPE_JWS,
        Jwk::ALGORITHM_HS384 => self::ALG_TYPE_JWS,
        Jwk::ALGORITHM_HS512 => self::ALG_TYPE_JWS,
        Jwk::ALGORITHM_A128KW => self::ALG_TYPE_JWE,
        Jwk::ALGORITHM_A192KW => self::ALG_TYPE_JWE,
        Jwk::ALGORITHM_A256KW => self::ALG_TYPE_JWE,
        Jwk::ALGORITHM_A128GCMKW => self::ALG_TYPE_JWE,
        Jwk::ALGORITHM_A192GCMKW => self::ALG_TYPE_JWE,
        Jwk::ALGORITHM_A256GCMKW => self::ALG_TYPE_JWE,
    ];

    /**
     * @var array
     */
    private $algs;

    /**
     * @param array $algs Additional algorithms.
     */
    public function __construct(array $algs = [])
    {
        $this->algs = array_merge($algs, self::ALGS);
    }

    public function getAlgorithmType(string $alg): int
    {
        return $this->algs[$alg];
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $options = [];
        foreach (array_keys($this->algs) as $algorithm) {
            $options[] = [
                'label' => __($algorithm . implode('', [' (', self::ALG_TYPE_NAME[$this->algs[$algorithm]], ')'])),
                'value' => $algorithm
            ];
        }

        return $options;
    }
}
