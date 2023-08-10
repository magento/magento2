<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\CustomAttribute;

/**
 * Interface for attribute preprocessor
 *
 * @api
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
    public function shouldBeProcessed(string $key, $attribute): bool;

    /**
     * Process attribute object according to type rules
     *
     * @param string $key
     * @param mixed $attribute
     */
    public function process(string $key, &$attribute);

    /**
     * Get list of affected attributes for the current preprocessor
     *
     * @return array
     */
    public function getAffectedAttributes(): array;
}
