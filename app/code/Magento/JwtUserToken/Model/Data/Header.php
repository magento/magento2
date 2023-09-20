<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model\Data;

use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\HeaderParameterInterface;

class Header implements HeaderInterface
{
    /**
     * @var HeaderParameterInterface[]
     */
    private $params;

    /**
     * @param HeaderParameterInterface[] $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * @inheritDoc
     */
    public function getParameters(): array
    {
        return $this->params;
    }

    /**
     * @inheritDoc
     */
    public function getParameter(string $name): ?HeaderParameterInterface
    {
        return array_key_exists($name, $this->params) ? $this->params[$name] : null;
    }
}
