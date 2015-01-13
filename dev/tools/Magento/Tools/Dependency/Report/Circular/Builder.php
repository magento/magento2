<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Dependency\Report\Circular;

use Magento\Tools\Dependency\Circular;
use Magento\Tools\Dependency\ParserInterface;
use Magento\Tools\Dependency\Report\Builder\AbstractBuilder;
use Magento\Tools\Dependency\Report\WriterInterface;

/**
 *  Dependencies report builder
 */
class Builder extends AbstractBuilder
{
    /**
     * Circular dependencies builder
     *
     * @var \Magento\Tools\Dependency\Circular
     */
    protected $circularBuilder;

    /**
     * Builder constructor
     *
     * @param \Magento\Tools\Dependency\ParserInterface $dependenciesParser
     * @param \Magento\Tools\Dependency\Report\WriterInterface $reportWriter
     * @param \Magento\Tools\Dependency\Circular $circularBuilder
     */
    public function __construct(
        ParserInterface $dependenciesParser,
        WriterInterface $reportWriter,
        Circular $circularBuilder
    ) {
        parent::__construct($dependenciesParser, $reportWriter);

        $this->circularBuilder = $circularBuilder;
    }

    /**
     * Template method. Prepare data for writer step
     *
     * @param array $modulesData
     * @return \Magento\Tools\Dependency\Report\Circular\Data\Config
     */
    protected function buildData($modulesData)
    {
        $modules = [];
        foreach ($this->buildCircularDependencies($modulesData) as $moduleName => $modulesChains) {
            $chains = [];
            foreach ($modulesChains as $modulesChain) {
                $chains[] = new Data\Chain($modulesChain);
            }
            $modules[] = new Data\Module($moduleName, $chains);
        }
        return new Data\Config($modules);
    }

    /**
     * Build circular dependencies data by dependencies data
     *
     * @param array $modulesData
     * @return array
     */
    protected function buildCircularDependencies($modulesData)
    {
        $dependencies = [];
        foreach ($modulesData as $moduleData) {
            foreach ($moduleData['dependencies'] as $dependencyData) {
                $dependencies[$moduleData['name']][] = $dependencyData['module'];
            }
        }
        return $this->circularBuilder->buildCircularDependencies($dependencies);
    }
}
