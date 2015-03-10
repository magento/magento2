<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Option;

use Symfony\Component\Console\Input\InputOption;

/**
 * Abstract Option class in deployment configuration tool
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
     * Constructor
     *
     * @param string $name
     * @param string $frontendType
     * @param string $description
     * @param int $mode
     * @param string|array|null $defaultValue
     * @param string|array|null $shortcut
     */
    public function __construct(
        $name,
        $frontendType,
        $mode,
        $description = '',
        $defaultValue = null,
        $shortcut = null
    ) {
        $this->frontendType = $frontendType;
        parent::__construct($name, $shortcut, $mode, $description, $defaultValue);
    }

    /**
     * Get frontend input type
     *
     * @return string
     */
    public function getFrontendType()
    {
        return $this->frontendType;
    }

    /**
     * No base validation
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @param mixed $data
     * @return void
     */
    public function validate($data)
    {
    }
}
