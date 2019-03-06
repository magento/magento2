<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\CustomAttribute;

/**
 * Interface for attribute preprocessor
 */
interface PreprocessorInterface
{
    /**
     * Check if this attribute data should be processed
     *
     * @param string $key
     * @param mixed $attribute
     * @return bool
     */
    public function shouldBeProcessed($key, $attribute);

    /**
     * Process attribute object according to type rules
     *
     * @param string $key
     * @param mixed $attribute
     */
    public function process($key, &$attribute);
}
