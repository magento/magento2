<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Math\Random;

/**
<<<<<<< HEAD
 * Generates a crypt
=======
 * Generates a crypt.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
     * The key length is not a parameter, but an implementation detail.
     *
     * @return string
     *
=======
     * @return string
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generate()
    {
        return md5($this->getRandomString());
    }

    /**
     * Returns a random string.
     *
     * @return string
<<<<<<< HEAD
=======
     * @throws \Magento\Framework\Exception\LocalizedException
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    private function getRandomString()
    {
        return $this->random->getRandomString(ConfigOptionsListConstants::STORE_KEY_RANDOM_STRING_SIZE);
    }
}
