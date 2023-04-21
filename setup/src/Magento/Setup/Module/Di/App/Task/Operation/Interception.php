<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Framework\App;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Setup\Module\Di\Code\Generator\InterceptionConfigurationBuilder;
use Magento\Setup\Module\Di\Code\GeneratorFactory;
use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;

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
    private $data;

    /**
     * @var ClassesScanner
     */
    private $classesScanner;

    /**
     * @var GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @param InterceptionConfigurationBuilder $interceptionConfigurationBuilder
     * @param App\AreaList $areaList
     * @param ClassesScanner $classesScanner
     * @param GeneratorFactory $generatorFactory
     * @param array $data
     */
    public function __construct(
        InterceptionConfigurationBuilder $interceptionConfigurationBuilder,
        App\AreaList $areaList,
        ClassesScanner $classesScanner,
        GeneratorFactory $generatorFactory,
        $data = []
    ) {
        $this->interceptionConfigurationBuilder = $interceptionConfigurationBuilder;
        $this->areaList = $areaList;
        $this->data = $data;
        $this->classesScanner = $classesScanner;
        $this->generatorFactory = $generatorFactory;
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
                $classesList[] = $this->classesScanner->getList($path);
            }
        }
        $classesList = array_merge([], ...$classesList);

        $generatorIo = new Io(new File(), $this->data['path_to_store']);
        $generator = $this->generatorFactory->create(
            [
                'ioObject' => $generatorIo,
                'generatedEntities' => [
                    Interceptor::ENTITY_TYPE => \Magento\Setup\Module\Di\Code\Generator\Interceptor::class,
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
