<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup;

use Magento\Framework\Math\Random;
use Magento\Framework\Setup\ConfigOptionsInterface;
use Magento\Framework\Setup\TextConfigOption;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptions implements ConfigOptionsInterface
{
    /**
     * Path to the values in the deployment config
     */
    const CONFIG_PATH_INSTALL_DATE = 'install/date';

    /**
     * Input key for encryption key
     */
    const INPUT_KEY_CRYPT_KEY = 'key';

    /**
     * @var array
     */
    private $options;

    /**
     * @var Random
     */
    private $random;

    /**
     * Constructor
     *
     * @param Random $random
     */
    public function __construct(Random $random)
    {
        $this->random = $random;
        $this->options = [new TextConfigOption('key', TextConfigOption::FRONTEND_WIZARD_TEXT, 'encryption key')];
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $data)
    {
        $config = [];
        $config['install']['date'] = date('r');
        if (isset($data[self::INPUT_KEY_CRYPT_KEY]) && !$data[self::INPUT_KEY_CRYPT_KEY]) {
            throw new \InvalidArgumentException('Invalid encryption key.');
        }
        if (!isset($data[self::INPUT_KEY_CRYPT_KEY])) {
            $config['crypt']['key'] = md5($this->random->getRandomString(10));
        } else {
            $config['crypt']['key'] = $data[self::INPUT_KEY_CRYPT_KEY];
        }
        return $config;
    }
}
