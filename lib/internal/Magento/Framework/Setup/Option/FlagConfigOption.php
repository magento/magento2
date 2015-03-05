<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Option;

/**
 * Flag option in deployment config tool
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
     * @param string|null $shortCut
     */
    public function __construct(
        $name,
        $description = '',
        $shortCut = null
    ) {
        parent::__construct($name, self::FRONTEND_WIZARD_FLAG, $description, self::VALUE_NONE, null, $shortCut);
    }
}
