<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Framework\App;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Generator\InterceptionConfigurationBuilder;
use Magento\Setup\Module\Di\Code\Generator\Interceptor as InterceptorGenerator;
use Magento\Setup\Module\Di\Code\GeneratorFactory;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;

/**
 * Class Interception
 */
class Interception implements OperationInterface
{
    /**
     * @var App\AreaList
     */
    private $areaList;

    /**
     * @var InterceptionConfigurationBuilder
     */
    private $interceptionConfigurationBuilder;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var ClassesScanner
     */
    private $classesScanner;

    /**
     * @var GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var string
     */
    private $interceptorGeneratorClass;

    /**
     * @param InterceptionConfigurationBuilder $interceptionConfigurationBuilder
     * @param App\AreaList $areaList
     * @param ClassesScanner $classesScanner
     * @param GeneratorFactory $generatorFactory
     * @param string $interceptorGeneratorClass
     * @param array $data
     */
    public function __construct(
        InterceptionConfigurationBuilder $interceptionConfigurationBuilder,
        App\AreaList $areaList,
        ClassesScanner $classesScanner,
        GeneratorFactory $generatorFactory,
        string $interceptorGeneratorClass = InterceptorGenerator::class,
        $data = []
    ) {
        $this->interceptionConfigurationBuilder = $interceptionConfigurationBuilder;
        $this->areaList = $areaList;
        $this->data = $data;
        $this->classesScanner = $classesScanner;
        $this->generatorFactory = $generatorFactory;
        $this->interceptorGeneratorClass = $interceptorGeneratorClass;
    }

    /**
     * @inheritdoc
     */
    public function doOperation()
    {
        if (empty($this->data)) {
            return;
        }
        $this->interceptionConfigurationBuilder->addAreaCode(App\Area::AREA_GLOBAL);

        foreach ($this->areaList->getCodes() as $areaCode) {
            $this->interceptionConfigurationBuilder->addAreaCode($areaCode);
        }

        $classesList = [];
        foreach ($this->data['intercepted_paths'] as $paths) {
            if (!is_array($paths)) {
                $paths = (array)$paths;
            }
            foreach ($paths as $path) {
                $classesList = array_merge($classesList, $this->classesScanner->getList($path));
            }
        }

        $generatorIo = new \Magento\Framework\Code\Generator\Io(
            new \Magento\Framework\Filesystem\Driver\File(),
            $this->data['path_to_store']
        );
        $generator = $this->generatorFactory->create(
            [
                'ioObject' => $generatorIo,
                'generatedEntities' => [
                    Interceptor::ENTITY_TYPE => $this->interceptorGeneratorClass,
                ]
            ]
        );
        $configuration = $this->interceptionConfigurationBuilder->getInterceptionConfiguration($classesList);
        $generator->generateList($configuration);
    }

    /**
     * Returns operation name
     *
     * @return string
     */
    public function getName()
    {
        return 'Interceptors generation';
    }
}
