<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup;

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
     * Constructor
     */
    public function __construct()
    {
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
