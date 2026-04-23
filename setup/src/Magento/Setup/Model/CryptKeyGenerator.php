<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Math\Random;

/**
 * Generates a crypt.
 */
class CryptKeyGenerator implements CryptKeyGeneratorInterface
{
    /**
     * @var Random
     */
    private $random;

    /**
     * CryptKeyGenerator constructor.
     *
     * @param Random $random
     */
    public function __construct(Random $random)
    {
        $this->random = $random;
    }

    /**
     * Generates & returns a string to be used as crypt key.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generate()
    {
        return $this->getRandomString();
    }

    /**
     * Returns a random string.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getRandomString()
    {
        return ConfigOptionsListConstants::STORE_KEY_ENCODED_RANDOM_STRING_PREFIX .
            $this->random->getRandomBytes(ConfigOptionsListConstants::STORE_KEY_RANDOM_STRING_SIZE);
    }
}
