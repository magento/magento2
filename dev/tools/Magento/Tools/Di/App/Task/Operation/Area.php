<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\App\Task\Operation;

use Magento\Tools\Di\App\Task\OperationInterface;
use Magento\Framework\App;
use Magento\Tools\Di\Code\Reader\ClassesScanner;
use Magento\Tools\Di\Compiler\Config;
use Magento\Tools\Di\Definition\Collection as DefinitionsCollection;

class Area implements OperationInterface
{
    /**
     * @var App\AreaList
     */
    private $areaList;

    /**
     * @var \Magento\Tools\Di\Code\Reader\InstancesNamesList\Area
     */
    private $areaInstancesNamesList;

    /**
     * @var Config\Reader
     */
    private $configReader;

    /**
     * @var Config\WriterInterface
     */
    private $configWriter;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param App\AreaList $areaList
     * @param \Magento\Tools\Di\Code\Reader\InstancesNamesList\Area $instancesNamesList
     * @param Config\Reader $configReader
     * @param Config\WriterInterface $configWriter
     * @param array $data
     */
    public function __construct(
        App\AreaList $areaList,
        \Magento\Tools\Di\Code\Reader\InstancesNamesList\Area $areaInstancesNamesList,
        Config\Reader $configReader,
        Config\WriterInterface $configWriter,
        $data = []
    ) {
        $this->areaList = $areaList;
        $this->areaInstancesNamesList = $areaInstancesNamesList;
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function doOperation()
    {
        if (empty($this->data)) {
            return;
        }

        $definitionsCollection = new DefinitionsCollection();
        foreach ($this->data as $path) {
            $definitionsCollection->addCollection($this->getDefinitionsCollection($path));
        }

        $areaCodes = array_merge([App\Area::AREA_GLOBAL], $this->areaList->getCodes());
        foreach ($areaCodes as $areaCode) {
            $this->configWriter->write(
                $areaCode,
                $this->configReader->generateCachePerScope($definitionsCollection, $areaCode)
            );
        }
    }

    /**
     * Returns definitions collection
     *
     * @param string $path
     * @return DefinitionsCollection
     */
    protected function getDefinitionsCollection($path)
    {
        $definitions = new DefinitionsCollection();
        foreach ($this->areaInstancesNamesList->getList($path) as $className => $constructorArguments) {
            $definitions->addDefinition($className, $constructorArguments);
        }
        return $definitions;
    }
}
