<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
