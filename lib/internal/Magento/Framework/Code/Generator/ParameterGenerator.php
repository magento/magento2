<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\Generator;

/**
 * Render sensitive parameter attributes alongside Laminas parameter declarations.
 */
class ParameterGenerator extends \Laminas\Code\Generator\ParameterGenerator
{
    /**
     * @var bool
     */
    private bool $sensitiveParameter = false;

    /**
     * Set whether the parameter must be redacted in stack traces.
     *
     * @param bool $sensitiveParameter
     * @return $this
     */
    public function setSensitiveParameter(bool $sensitiveParameter): self
    {
        $this->sensitiveParameter = $sensitiveParameter;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        return ($this->sensitiveParameter ? '#[\\SensitiveParameter] ' : '') . parent::generate();
    }
}
