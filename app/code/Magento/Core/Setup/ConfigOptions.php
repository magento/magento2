<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Setup;

use Magento\Framework\Setup\ConfigOptionsInterface;
use Magento\Framework\Setup\TextConfigOption;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptions implements ConfigOptionsInterface
{
    /**
     * Input key for encryption key
     */
    const INPUT_KEY_CRYPT_KEY = 'key';

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [new TextConfigOption('key', TextConfigOption::FRONTEND_WIZARD_TEXT, 'encryption key')];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $data)
    {
        $config = [];
        if (!isset($data[self::INPUT_KEY_CRYPT_KEY])) {
            throw new \InvalidArgumentException('No encryption key provided.');
        }
        if (!$data[self::INPUT_KEY_CRYPT_KEY]) {
            throw new \InvalidArgumentException('Invalid encryption key.');
        }
        $config['crypt']['key'] = $data[self::INPUT_KEY_CRYPT_KEY];
        return $config;
    }
}
