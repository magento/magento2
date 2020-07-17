<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Option;

/**
 * Select option in deployment config tool
 */
class BooleanConfigOption extends AbstractConfigOption
{
    /**#@+
     * Frontend input types
     */
    const FRONTEND_WIZARD_RADIO = 'radio';

    const SELECT_OPTIONS = ['no', 'yes'];
    const INPUT_OPTIONS = ['0', '1', 'no', 'yes', 'false', 'true'];
    const OPTIONS_POSITIVE = ['1', 'yes', 'true'];
    /**#@- */

    /**
     * Constructor
     *
     * @param string $name
     * @param string $frontendType
     * @param string $configPath
     * @param string $description
     * @param string|null $defaultValue
     * @param string|array|null $shortCut
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $name,
        $configPath,
        $description = '',
        $defaultValue = '0',
        $shortCut = null
    ) {
        parent::__construct(
            $name,
            self::FRONTEND_WIZARD_RADIO,
            self::VALUE_REQUIRED,
            $configPath,
            $description,
            $defaultValue,
            $shortCut
        );
    }

    /**
     * Get available options
     *
     * @return array
     */
    public function getSelectOptions()
    {
        return self::SELECT_OPTIONS;
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
        if (!in_array(strtolower($data), self::INPUT_OPTIONS)) {
            throw new \InvalidArgumentException("Value specified for '{$this->getName()}' is not supported: '{$data}'");
        }
        parent::validate($data);
    }

    /**
     * @param string $option
     *
     * @return bool
     */
    public static function boolVal(string $option): bool
    {
        return in_array(strtolower($option), self::OPTIONS_POSITIVE);
    }
}
