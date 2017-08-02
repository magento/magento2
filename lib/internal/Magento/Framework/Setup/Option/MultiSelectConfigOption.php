<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Option;

/**
 * Multi-select option in deployment config tool
 * @since 2.0.0
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
     * Available options
     *
     * @var array
     * @since 2.0.0
     */
    private $selectOptions;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $frontendType
     * @param array $selectOptions
     * @param string $configPath
     * @param string $description
     * @param array $defaultValue
     * @param string|array|null $shortCut
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function __construct(
        $name,
        $frontendType,
        array $selectOptions,
        $configPath,
        $description = '',
        array $defaultValue = [],
        $shortCut = null
    ) {
        if ($frontendType != self::FRONTEND_WIZARD_MULTISELECT && $frontendType != self::FRONTEND_WIZARD_CHECKBOX) {
            throw new \InvalidArgumentException(
                "Frontend input type has to be 'multiselect', 'textarea' or 'checkbox'."
            );
        }
        if (!$selectOptions) {
            throw new \InvalidArgumentException('Select options can\'t be empty.');
        }
        $this->selectOptions = $selectOptions;
        parent::__construct(
            $name,
            $frontendType,
            self::VALUE_REQUIRED | self::VALUE_IS_ARRAY,
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
     * @since 2.0.0
     */
    public function getSelectOptions()
    {
        return $this->selectOptions;
    }

    /**
     * Validates input data
     *
     * @param mixed $data
     * @return void
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function validate($data)
    {
        if (is_array($data)) {
            foreach ($data as $value) {
                if (!in_array($value, $this->getSelectOptions())) {
                    throw new \InvalidArgumentException(
                        "Value specified for '{$this->getName()}' is not supported: '{$value}'"
                    );
                }
            }
        }
        parent::validate($data);
    }
}
