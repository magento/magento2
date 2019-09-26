<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model;

use Magento\Framework\Jwt\ManagementInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * JSON Web Token management.
 */
class JwtManagement
{
    /**
     * @var ManagementInterface
     */
    private $management;

    /**
     * @param ManagementInterface $management
     */
    public function __construct(
        ManagementInterface $management
    ) {
        $this->management = $management;
    }

    /**
     * Converts JWT string into array.
     *
     * @param string $jwt The JWT
     * @return array
     * @throws \InvalidArgumentException
     */
    public function decode(string $jwt): array
    {
        if (empty($jwt)) {
            throw new \InvalidArgumentException('JWT is empty');
        }

        return $this->management->decode($jwt);
    }

    /**
     * Converts and signs array into a JWT string.
     *
     * @param array $payload
     * @return string
     * @throws \Exception
     */
    public function encode(array $payload): string
    {
        return $this->management->encode($payload);
    }
}
