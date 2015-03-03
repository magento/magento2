<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Symfony\Component\Console\Input\InputOption;

class Option extends InputOption
{
    /**#@+
     * Frontend input types
     */
    const FRONTEND_WIZARD_TEXT = 'text';
    const FRONTEND_WIZARD_PASSWORD = 'password';
    const FRONTEND_WIZARD_RADIO = 'radio';
    const FRONTEND_WIZARD_CHECKBOX = 'checkbox';
    const FRONTEND_WIZARD_SELECT = 'select';
    const FRONTEND_WIZARD_MULTISELECT = 'multiselect';
    const FRONTEND_WIZARD_TEXTAREA = 'textarea';
    /**#@- */

    /**
     * Frontend input type
     *
     * @var string
     */
    private $frontendType;

    /**
     * Available options
     *
     * @var array
     */
    private $selectOptions;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $description
     * @param string $frontendType
     * @param array $selectOptions
     * @param string|null $default
     * @param int|null $mode
     * @param string|null $shortcut
     */
    public function __construct(
        $name,
        $description = '',
        $frontendType,
        array $selectOptions = [],
        $default = null,
        $mode = null,
        $shortcut = null
    ) {
        if ($frontendType != self::FRONTEND_WIZARD_TEXT || $frontendType != self::FRONTEND_WIZARD_CHECKBOX ||
            $frontendType != self::FRONTEND_WIZARD_MULTISELECT || $frontendType != self::FRONTEND_WIZARD_PASSWORD ||
            $frontendType != self::FRONTEND_WIZARD_RADIO || $frontendType != self::FRONTEND_WIZARD_SELECT ||
            $frontendType != self::FRONTEND_WIZARD_TEXTAREA
        ) {
            throw new \InvalidArgumentException('Unknown frontend input type.');
        }
        $this->frontendType = $frontendType;
        $this->selectOptions = $selectOptions;
        parent::__construct($name, $shortcut = null, $mode = null, $description = '', $default = null);
    }

    /**
     * Get frontend input type
     *
     * @return string
     */
    public function getFrontendInput()
    {
        $this->frontendType;
    }

    /**
     * Get available options
     *
     * @return array
     */
    public function getSelectOptions()
    {
        $this->selectOptions;
    }
}
