<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Header;

use Magento\Framework\Jwt\Jwe\JweHeaderParameterInterface;
use Magento\Framework\Jwt\Jws\JwsHeaderParameterInterface;
use Magento\Framework\Jwt\Jwk as JwkData;

/**
 * "jwk" header.
 */
class Jwk implements JwsHeaderParameterInterface, JweHeaderParameterInterface
{
    /**
     * @var JwkData
     */
    private $value;

    /**
     * @param JwkData $value
     */
    public function __construct(JwkData $value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'jwk';
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return json_encode($this->value->getJsonData());
    }

    /**
     * @inheritDoc
     */
    public function getClass(): ?int
    {
        return self::CLASS_REGISTERED;
    }
}
