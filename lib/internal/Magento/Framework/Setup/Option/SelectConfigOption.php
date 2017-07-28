<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Option;

/**
 * Select option in deployment config tool
 * @since 2.0.0
 */
class SelectConfigOption extends AbstractConfigOption
{
    /**#@+
     * Frontend input types
     */
    const FRONTEND_WIZARD_RADIO = 'radio';
    const FRONTEND_WIZARD_SELECT = 'select';
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
     * @param string|null $defaultValue
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
        $defaultValue = null,
        $shortCut = null
    ) {
        if ($frontendType != self::FRONTEND_WIZARD_SELECT && $frontendType != self::FRONTEND_WIZARD_RADIO) {
            throw new \InvalidArgumentException("Frontend input type has to be 'select' or 'radio'.");
        }
        if (!$selectOptions) {
            throw new \InvalidArgumentException('Select options can\'t be empty.');
        }
        $this->selectOptions = $selectOptions;
        parent::__construct(
            $name,
            $frontendType,
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
        if (!in_array($data, $this->getSelectOptions())) {
            throw new \InvalidArgumentException("Value specified for '{$this->getName()}' is not supported: '{$data}'");
        }
        parent::validate($data);
    }
}
