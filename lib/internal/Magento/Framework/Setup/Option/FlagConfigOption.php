<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Option;

/**
 * Flag option in deployment config tool
 * @since 2.0.0
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
     * @param string $configPath
     * @param string $description
     * @param string|array|null $shortCut
     * @since 2.0.0
     */
    public function __construct(
        $name,
        $configPath,
        $description = '',
        $shortCut = null
    ) {
        parent::__construct(
            $name,
            self::FRONTEND_WIZARD_FLAG,
            self::VALUE_NONE,
            $configPath,
            $description,
            null,
            $shortCut
        );
    }
}
