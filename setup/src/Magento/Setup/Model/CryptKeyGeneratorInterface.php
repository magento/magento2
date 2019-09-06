<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

/**
 * Interface for crypt key generators.
 */
interface CryptKeyGeneratorInterface
{
    /**
     * Generates & returns a string to be used as crypt key.
     *
     * The key length is not a parameter, but an implementation detail.
     *
     * @return string
     */
    public function generate();
}
