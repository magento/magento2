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
     * Check if this attribute data should be prepared
     *
     * @param string $key
     * @param mixed $attribute
     * @return bool
     */
    public function shouldBePrepared($key, $attribute);

    /**
     * Prepare attribute object according to type rules
     *
     * @param string $key
     * @param mixed $attribute
     */
    public function prepare($key, &$attribute);
}
