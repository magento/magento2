<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Mtf;

use Magento\Framework\App\Filesystem\DirectoryList;
use Mtf\ObjectManagerInterface as MagentoObjectManager;
use Mtf\Stdlib\BooleanUtils;
use Mtf\ObjectManager\Factory;
use Mtf\System\Config as SystemConfig;

/**
 * Class ObjectManagerFactory
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObjectManagerFactory
{
    /**
     * Object Manager class name
     *
     * @var string
     */
    protected $locatorClassName = '\Mtf\ObjectManager';

    /**
     * DI Config class name
     *
     * @var string
     */
    protected $configClassName = '\Mtf\ObjectManager\Config';

    /**
     * Create Object Manager
     *
     * @param array $sharedInstances
     * @return ObjectManager
     */
    public function create(array $sharedInstances = [])
    {
        if (!defined('MTF_BP')) {
            $basePath = str_replace('\\', '/', dirname(dirname(__DIR__)));
            define('MTF_BP', $basePath);
        }
        if (!defined('MTF_TESTS_PATH')) {
            define('MTF_TESTS_PATH', MTF_BP . '/tests/app/');
        }
        if (!defined('MTF_STATES_PATH')) {
            define('MTF_STATES_PATH', MTF_BP . '/lib/Mtf/App/State/');
        }

        $diConfig = new $this->configClassName();
        $factory = new Factory($diConfig);
        $argInterpreter = $this->createArgumentInterpreter(new BooleanUtils());
        $argumentMapper = new \Mtf\ObjectManager\Config\Mapper\Dom($argInterpreter);

        $sharedInstances['Mtf\ObjectManager\Config\Mapper\Dom'] = $argumentMapper;
        $objectManager = new $this->locatorClassName($factory, $diConfig, $sharedInstances);

        $factory->setObjectManager($objectManager);
        ObjectManager::setInstance($objectManager);

        self::configure($objectManager);

        return $objectManager;
    }

    /**
     * Create instance of application deployment config
     *
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param array $arguments
     * @return \Magento\Framework\App\DeploymentConfig
     */
    protected function createDeploymentConfig(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        array $arguments
    ) {
        return new \Magento\Framework\App\DeploymentConfig(
            new \Magento\Framework\App\DeploymentConfig\Reader($directoryList),
            isset($arguments[\Magento\Framework\App\Arguments\Loader::PARAM_CUSTOM_FILE])
                ? $arguments[\Magento\Framework\App\Arguments\Loader::PARAM_CUSTOM_FILE]
                : null
        );
    }

    /**
     * Return newly created instance on an argument interpreter, suitable for processing DI arguments
     *
     * @param \Mtf\Stdlib\BooleanUtils $booleanUtils
     * @return \Mtf\Data\Argument\InterpreterInterface
     */
    protected function createArgumentInterpreter(
        \Mtf\Stdlib\BooleanUtils $booleanUtils
    ) {
        $constInterpreter = new \Mtf\Data\Argument\Interpreter\Constant();
        $result = new \Mtf\Data\Argument\Interpreter\Composite(
            [
                'boolean' => new \Mtf\Data\Argument\Interpreter\Boolean($booleanUtils),
                'string' => new \Mtf\Data\Argument\Interpreter\String($booleanUtils),
                'number' => new \Mtf\Data\Argument\Interpreter\Number(),
                'null' => new \Mtf\Data\Argument\Interpreter\NullType(),
                'const' => $constInterpreter,
                'object' => new \Mtf\Data\Argument\Interpreter\Object($booleanUtils),
                'init_parameter' => new \Mtf\Data\Argument\Interpreter\Argument($constInterpreter),
            ],
            \Mtf\ObjectManager\Config\Reader\Dom::TYPE_ATTRIBUTE
        );
        // Add interpreters that reference the composite
        $result->addInterpreter('array', new \Mtf\Data\Argument\Interpreter\ArrayType($result));
        return $result;
    }

    /**
     * Get MTF Object Manager instance
     *
     * @return ObjectManager
     */
    public static function getObjectManager()
    {
        if (!$objectManager = ObjectManager::getInstance()) {
            $objectManagerFactory = new self();
            $objectManager = $objectManagerFactory->create();
        }

        return $objectManager;
    }

    /**
     * Configure Object Manager
     * This method is static to have the ability to configure multiple instances of Object manager when needed
     *
     * @param MagentoObjectManager $objectManager
     */
    public static function configure(MagentoObjectManager $objectManager)
    {
        $objectManager->configure(
            $objectManager->get('Mtf\ObjectManager\ConfigLoader\Primary')->load()
        );

        $objectManager->configure(
            $objectManager->get('Mtf\ObjectManager\ConfigLoader\Module')->load()
        );

        $objectManager->configure(
            $objectManager->get('Mtf\ObjectManager\ConfigLoader\Module')->load('etc/ui')
        );

        $objectManager->configure(
            $objectManager->get('Mtf\ObjectManager\ConfigLoader\Module')->load('etc/curl')
        );
    }
}
