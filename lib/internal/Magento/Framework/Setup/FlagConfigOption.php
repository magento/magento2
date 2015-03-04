<?php
namespace Magento\Framework\Setup;

/**
 * Flag config option in deployment config
 */
class FlagConfigOption extends AbstractConfigOption
{
    /**
     * Frontend input types
     */
    const FRONTEND_WIZARD_FLAG = 'flag';

    /**
     * Constructor
     *
     * @param string $name
     * @param string $description
     * @param string $default
     * @param string|null $shortCut
     */
    public function __construct(
        $name,
        $description = '',
        $default = '',
        $shortCut = null
    ) {
        parent::__construct($name, self::FRONTEND_WIZARD_FLAG, $description, null, self::VALUE_NONE, $shortCut);
    }
}
