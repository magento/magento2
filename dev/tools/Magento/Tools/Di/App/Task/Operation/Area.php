<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\Di\App\Task\Operation;

use Magento\Tools\Di\App\Task\OperationInterface;
use Magento\Framework\App;
use Magento\Tools\Di\Code\Reader\ClassesScanner;
use Magento\Tools\Di\Compiler\Config;
use Magento\Tools\Di\Definition\Collection as DefinitionsCollection;
use Magento\Tools\Di\Code\Generator\InterceptionConfigurationBuilder;

class Area implements OperationInterface
{
    /**
     * @var App\AreaList
     */
    private $areaList;

    /**
     * @var ClassesScanner
     */
    private $classesScanner;

    /**
     * @var Config\Reader
     */
    private $configReader;

    /**
     * @var InterceptionConfigurationBuilder
     */
    private $interceptionConfigurationBuilder;

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
     * @param ClassesScanner $classesScanner
     * @param Config\Reader $configReader
     * @param InterceptionConfigurationBuilder $interceptionConfigurationBuilder
     * @param Config\WriterInterface $configWriter
     * @param array $data
     */
    public function __construct(
        App\AreaList $areaList,
        ClassesScanner $classesScanner,
        Config\Reader $configReader,
        InterceptionConfigurationBuilder $interceptionConfigurationBuilder,
        Config\WriterInterface $configWriter,
        $data = []
    ) {
        $this->areaList = $areaList;
        $this->classesScanner = $classesScanner;
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
        $this->interceptionConfigurationBuilder = $interceptionConfigurationBuilder;
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

        $this->configWriter->write(
            App\Area::AREA_GLOBAL,
            $this->configReader->generateCachePerScope($definitionsCollection, App\Area::AREA_GLOBAL)
        );
        $this->interceptionConfigurationBuilder->addAreaCode(App\Area::AREA_GLOBAL);
        foreach ($this->areaList->getCodes() as $areaCode) {
            $this->interceptionConfigurationBuilder->addAreaCode($areaCode);
            $this->configWriter->write(
                $areaCode,
                $this->configReader->generateCachePerScope($definitionsCollection, $areaCode, true)
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
        foreach ($this->classesScanner->getList($path) as $className => $constructorArguments) {
            $definitions->addDefinition($className, $constructorArguments);
        }
        return $definitions;
    }
}
