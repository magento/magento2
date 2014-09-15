<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Setup\Controller\Install;

use Magento\Framework\Math\Random;
use Magento\Module\ModuleListInterface;
use Magento\Module\Setup\Config;
use Magento\Module\SetupFactory;
use Magento\Setup\Model\AdminAccountFactory;
use Magento\Setup\Model\Logger;
use Magento\Config\ConfigFactory;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class StartController extends AbstractActionController
{
    /**
     * @var JsonModel
     */
    protected $json;

    /**
     * @var []
     */
    protected $moduleList;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ConfigFactory
     */
    protected $systemConfig;

    /**
     * @var AdminAccountFactory
     */
    protected $adminAccountFactory;

    /**
     * @var Random
     */
    protected $random;

    /**
     * @param JsonModel $view
     * @param ModuleListInterface $moduleList
     * @param SetupFactory $setupFactory
     * @param AdminAccountFactory $adminAccountFactory
     * @param Logger $logger
     * @param Random $random
     * @param Config $config
     */
    public function __construct(
        JsonModel $view,
        ModuleListInterface $moduleList,
        SetupFactory $setupFactory,
        AdminAccountFactory $adminAccountFactory,
        Logger $logger,
        Random $random,
        Config $config,
        ConfigFactory $systemConfig
    ) {
        $this->logger = $logger;
        $this->json = $view;
        $this->moduleList = $moduleList->getModules();
        $this->setupFactory = $setupFactory;
        $this->config = $config;
        $this->systemConfig = $systemConfig;
        $this->adminAccountFactory = $adminAccountFactory;
        $this->random = $random;
    }

    /**
     * @return JsonModel
     */
    public function indexAction()
    {
        $this->logger->clear();

        $data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);

        $this->config->setConfigData($data);
        $this->config->install();

        $this->setupFactory->setConfig($this->config->getConfigData());

        $moduleNames = array_keys($this->moduleList);
        foreach ($moduleNames as $moduleName) {
            $setup = $this->setupFactory->create($moduleName);
            $setup->applyUpdates();
            $this->logger->logSuccess($moduleName);
        }
        $this->logger->logSuccess('Artifact');

        // Set data to config
        $setup->addConfigData(
            'web/seo/use_rewrites',
            isset($data['config']['rewrites']['allowed']) ? $data['config']['rewrites']['allowed'] : 0
        );

        $setup->addConfigData(
            'web/unsecure/base_url',
            isset($data['config']['address']['web']) ? $data['config']['address']['web'] : '{{unsecure_base_url}}'
        );
        $setup->addConfigData(
            'web/secure/use_in_frontend',
            isset($data['config']['https']['front']) ? $data['config']['https']['front'] : 0
        );
        $setup->addConfigData(
            'web/secure/base_url',
            isset($data['config']['address']['web']) ? $data['config']['address']['web'] : '{{secure_base_url}}'
        );
        $setup->addConfigData(
            'web/secure/use_in_adminhtml',
            isset($data['config']['https']['admin']) ? $data['config']['https']['admin'] : 0
        );
        $setup->addConfigData(
            'general/locale/code',
            isset($data['store']['language']) ? $data['store']['language'] : 'en_US'
        );
        $setup->addConfigData(
            'general/locale/timezone',
            isset($data['store']['timezone']) ? $data['store']['timezone'] : 'America/Los_Angeles'
        );

        $currencyCode = isset($data['store']['currency']) ? $data['store']['currency'] : 'USD';

        $setup->addConfigData('currency/options/base', $currencyCode);
        $setup->addConfigData('currency/options/default', $currencyCode);
        $setup->addConfigData('currency/options/allow', $currencyCode);

        // Create administrator account
        $this->adminAccountFactory->setConfig($this->config->getConfigData());
        $adminAccount = $this->adminAccountFactory->create($setup);
        $adminAccount->save();

        $this->logger->logSuccess('Admin User');

        if ($data['config']['encrypt']['type'] == 'magento') {
            $key = md5($this->random->getRandomString(10));
        } else {
            $key = $data['config']['encrypt']['key'];
        }

        $this->config->replaceTmpEncryptKey($key);
        $this->config->replaceTmpInstallDate(date('r'));

        $phpPath = $this->phpExecutablePath();
        exec(
            $phpPath .
            'php -f ' . escapeshellarg($this->systemConfig->create()->getMagentoBasePath() .
                '/dev/shell/run_data_fixtures.php'),
            $output,
            $exitCode
        );
        if ($exitCode !== 0) {
            $outputMsg = implode(PHP_EOL, $output);
            $this->logger->logError(
                new \Exception('Data Update Failed with Exit Code: ' . $exitCode . PHP_EOL . $outputMsg)
            );
            $this->json->setVariable('success', false);
        } else {
            $this->logger->logSuccess('Data Updates');
            $this->json->setVariable('success', true);
        }

        $this->json->setVariable('key', $key);
        return $this->json;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function phpExecutablePath()
    {
        try {
            $phpPath = '';
            $iniFile = fopen(php_ini_loaded_file(), 'r');
            while ($line = fgets($iniFile)) {
                if ((strpos($line, 'extension_dir') !== false) && (strrpos($line, ";") !==0)) {
                    $extPath = explode("=", $line);
                    $pathFull = explode("\"", $extPath[1]);
                    $pathParts[1] = str_replace('\\', '/', $pathFull[1]);
                    foreach (explode('/', $pathParts[1]) as $piece) {
                        $phpPath .= $piece . '/';
                        if (strpos($piece, phpversion()) !== false) {
                            if (file_exists($phpPath.'bin')) {
                                $phpPath .= 'bin' . '/';
                            }
                            break;
                        }
                    }
                }
            }
            fclose($iniFile);
        } catch(\Exception $e){
            throw $e;
        }

        return $phpPath;
    }
}
