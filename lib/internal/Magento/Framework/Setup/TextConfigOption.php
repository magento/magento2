<?php
namespace Magento\Framework\Setup;

/**
 * Text config option in deployment config
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
        parent::__construct($name, $frontendType, $description, [], $default, self::VALUE_REQUIRED, $shortCut);
    }
}
