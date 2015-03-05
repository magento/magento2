<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManager\DefinitionFactory;
use Magento\Framework\Setup\ConfigOptionsInterface;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\Setup\Option\MultiSelectConfigOption;
use Magento\Framework\Module\ModuleList\Loader;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptions implements ConfigOptionsInterface
{
    /**
     * Path to the values in the deployment config
     */
    const CONFIG_PATH_INSTALL_DATE = 'install/date';
    const CONFIG_PATH_MODULES = 'modules';

    /**
     * Input keys for the options
     */
    const INPUT_KEY_CRYPT_KEY = 'key';
    const INPUT_KEY_SESSION_SAVE = 'session_save';
    const INPUT_KEY_DEFINITION_FORMAT = 'definition_format';

    /**
     * @var array
     */
    private $moduleList;

    /**
     * @var Random
     */
    private $random;

    /**
     * Constructor
     *
     * @param Random $random
     * @param Loader $moduleLoader
     */
    public function __construct(Random $random, Loader $moduleLoader)
    {
        $this->random = $random;
        $this->moduleList = $moduleLoader->load();
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [
            new TextConfigOption(
                self::INPUT_KEY_CRYPT_KEY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                'Encryption key'
            ),
            new MultiSelectConfigOption(
                self::CONFIG_PATH_MODULES,
                MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT,
                $this->moduleList,
                'modules list',
                $this->moduleList
            ),
            new SelectConfigOption(
                self::INPUT_KEY_SESSION_SAVE,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                ['files', 'db'],
                'Session save location',
                'files'
            ),
            new SelectConfigOption(
                self::INPUT_KEY_DEFINITION_FORMAT,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                DefinitionFactory::getSupportedFormats(),
                'Type of definitions used by Object Manager'
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $data)
    {
        $configData = [];
        // install segment
        $configData[] = new ConfigData(ConfigFilePool::APP_CONFIG, 'install', ['date' => date('r')]);

        // crypt segment
        if (isset($data[self::INPUT_KEY_CRYPT_KEY]) && !$data[self::INPUT_KEY_CRYPT_KEY]) {
            throw new \InvalidArgumentException('Invalid encryption key.');
        }
        $cryptData = [];
        if (!isset($data[self::INPUT_KEY_CRYPT_KEY])) {
            $cryptData['key'] = md5($this->random->getRandomString(10));
        } else {
            $cryptData['key'] = $data[self::INPUT_KEY_CRYPT_KEY];
        }
        $configData[] = new ConfigData(ConfigFilePool::APP_CONFIG, 'crypt', $cryptData);

        // module segment
        $modulesData = [];
        if (isset($this->moduleList)) {
            foreach ($this->moduleList as $key) {
                $modulesData[$key] = 1;
            }
        }
        $configData[] = new ConfigData(ConfigFilePool::APP_CONFIG, 'modules', $modulesData);

        // session segment
        $sessionData = [];
        if (isset($data[self::INPUT_KEY_SESSION_SAVE])) {
            if ($data[self::INPUT_KEY_SESSION_SAVE] != 'files' && $data[self::INPUT_KEY_SESSION_SAVE] != 'db') {
                throw new \InvalidArgumentException('Invalid session save location.');
            }
            $sessionData['save'] = $data[self::INPUT_KEY_SESSION_SAVE];
        } else {
            $sessionData['save'] = 'files';
        }
        $configData[] = new ConfigData(ConfigFilePool::APP_CONFIG, 'session', $sessionData);

        // definitions segment
        if (!empty($data[self::INPUT_KEY_DEFINITION_FORMAT])) {
            $config['definition']['format'] = $data[self::INPUT_KEY_DEFINITION_FORMAT];
            $configData[] = new ConfigData(
                ConfigFilePool::APP_CONFIG,
                'definition',
                ['format' => $data[self::INPUT_KEY_DEFINITION_FORMAT]]
            );
        }

        return $configData;
    }
}
