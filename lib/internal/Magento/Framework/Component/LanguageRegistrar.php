<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register language
 */
class LanguageRegistrar extends ComponentRegistrar
{
    /**
     * Paths to languages
     *
     * @var string[]
     */
    protected static $componentPaths = [];
}
