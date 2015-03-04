<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Symfony\Component\Console\Input\InputOption;

/**
 * Abstract Option class in a segment of the deployment configuration
 */
abstract class AbstractConfigOption extends InputOption
{
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
     * @param string $frontendType
     * @param string $description
     * @param array $selectOptions
     * @param string|null $default
     * @param int $mode
     * @param string|null $shortcut
     */
    public function __construct(
        $name,
        $frontendType,
        $description = '',
        array $selectOptions = [],
        $default = null,
        $mode,
        $shortcut = null
    ) {
        $this->frontendType = $frontendType;
        $this->selectOptions = $selectOptions;
        parent::__construct($name, $shortcut, $mode, $description, $default);
    }

    /**
     * Get frontend input type
     *
     * @return string
     */
    public function getFrontendInput()
    {
        return $this->frontendType;
    }

    /**
     * Get available options
     *
     * @return array
     */
    public function getSelectOptions()
    {
        return $this->selectOptions;
    }

    /**
     * No base validation
     *
     * @param mixed $data
     * @return void
     */
    public function validate($data)
    {
    }
}
