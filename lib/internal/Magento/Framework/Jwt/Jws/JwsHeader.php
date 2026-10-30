<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Jws;

use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\HeaderParameterInterface;

class JwsHeader implements HeaderInterface
{
    /**
     * @var JwsHeaderParameterInterface[]
     */
    private $parameters;

    /**
     * @param JwsHeaderParameterInterface[] $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = [];
        foreach ($parameters as $parameter) {
            if (!$parameter instanceof JwsHeaderParameterInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Header "%s" is not applicable to JWS tokens', $parameter->getName())
                );
            }
            $this->parameters[$parameter->getName()] = $parameter;
        }
    }

    /**
     * @inheritDoc
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function getParameter(string $name): ?HeaderParameterInterface
    {
        return !empty($this->parameters[$name]) ? $this->parameters[$name] : null;
    }
}
