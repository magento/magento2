<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * Text option in deployment config tool
 */
class TextConfigOption extends AbstractConfigOption
{
    /**#@+
     * Frontend input types
     */
    const FRONTEND_WIZARD_TEXT = 'text';
    const FRONTEND_WIZARD_PASSWORD = 'password';
    const FRONTEND_WIZARD_TEXTAREA = 'textarea';
    /**#@- */

    /**
     * Constructor
     *
     * @param string $name
     * @param string $frontendType
     * @param string $description
     * @param string|null $default
     * @param string|null $shortCut
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $name,
        $frontendType,
        $description = '',
        $default = null,
        $shortCut = null
    ) {
        if ($frontendType != self::FRONTEND_WIZARD_TEXT && $frontendType != self::FRONTEND_WIZARD_PASSWORD &&
            $frontendType != self::FRONTEND_WIZARD_TEXTAREA
        ) {
            throw new \InvalidArgumentException('Frontend input type has to be text, textarea or password.');
        }
        parent::__construct($name, $frontendType, $description, self::VALUE_REQUIRED, $default, $shortCut);
    }

    /**
     * Validates input data
     *
     * @param mixed $data
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validate($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException("'{$this->getName()}' must be a string");
        }
        parent::validate($data);
    }
}
