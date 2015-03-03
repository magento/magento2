<?php
namespace Magento\Framework\Setup;

/**
 * Multi-select config option in deployment config
 */
class MultiSelectConfigOption extends AbstractConfigOption
{
    /**#@+
     * Frontend input types
     */
    const FRONTEND_WIZARD_CHECKBOX = 'checkbox';
    const FRONTEND_WIZARD_MULTISELECT = 'multiselect';
    /**#@- */

    /**
     * Constructor
     *
     * @param string $name
     * @param string $frontendType
     * @param array $selectOptions
     * @param string $description
     * @param string $default
     * @param string|null $shortCut
     */
    public function __construct(
        $name,
        $frontendType,
        array $selectOptions,
        $description = '',
        $default = '',
        $shortCut = null
    ) {
        if ($frontendType != self::FRONTEND_WIZARD_MULTISELECT && $frontendType != self::FRONTEND_WIZARD_CHECKBOX) {
            throw new \InvalidArgumentException('Frontend input type has to be multiselect, textarea or checkbox.');
        }
        if (!$selectOptions) {
            throw new \InvalidArgumentException('Select options can\'t be empty.');
        }
        parent::__construct(
            $name,
            $frontendType,
            $description,
            $selectOptions,
            $default,
            self::VALUE_REQUIRED,
            $shortCut
        );
    }
}
