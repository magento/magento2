<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\ModuleList;

use Magento\Framework\Module\Declaration\Converter\Dom;
use Magento\Framework\Xml\Parser;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Loader of module list information from the filesystem
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Loader
{
    /**
     * Converter of XML-files to associative arrays (specific to module.xml file format)
     *
     * @var Dom
     */
    private $converter;

    /**
     * Parser
     *
     * @var \Magento\Framework\Xml\Parser
     */
    private $parser;

    /**
     * Module registry
     *
     * @var ComponentRegistrarInterface
     */
    private $moduleRegistry;

    /**
     * Filesystem driver to allow reading of module.xml files which live outside of app/code
     *
     * @var DriverInterface
     */
    private $filesystemDriver;

    /**
     * Constructor
     *
     * @param Dom $converter
     * @param Parser $parser
     * @param ComponentRegistrarInterface $moduleRegistry
     * @param DriverInterface $filesystemDriver
     */
    public function __construct(
        Dom $converter,
        Parser $parser,
        ComponentRegistrarInterface $moduleRegistry,
        DriverInterface $filesystemDriver
    ) {
        $this->converter = $converter;
        $this->parser = $parser;
        $this->parser->initErrorHandler();
        $this->moduleRegistry = $moduleRegistry;
        $this->filesystemDriver = $filesystemDriver;
    }

    /**
     * Loads the full module list information. Excludes modules specified in $exclude.
     *
     * @param array $exclude
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    public function load(array $exclude = [])
    {
        $result = [];
        foreach ($this->getModuleConfigs() as list($file, $contents)) {
            try {
                $this->parser->loadXML($contents);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(
                        'Invalid Document: %1%2 Error: %3',
                        [$file, PHP_EOL, $e->getMessage()]
                    ),
                    $e
                );
            }

            $data = $this->converter->convert($this->parser->getDom());
            $name = key($data);
            if (!in_array($name, $exclude)) {
                $result[$name] = $data[$name];
            }
        }
        return $this->sortBySequence($result);
    }

    /**
     * Returns module config data and a path to the module.xml file.
     *
     * Example of data returned by generator:
     * <code>
     *     [ 'vendor/module/etc/module.xml', '<xml>contents</xml>' ]
     * </code>
     *
     * @return \Traversable
     *
     * @author Josh Di Fabio <joshdifabio@gmail.com>
     */
    private function getModuleConfigs()
    {
        $modulePaths = $this->moduleRegistry->getPaths(ComponentRegistrar::MODULE);
        foreach ($modulePaths as $modulePath) {
            $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, "$modulePath/etc/module.xml");
            yield [$filePath, $this->filesystemDriver->fileGetContents($filePath)];
        }
    }

    /**
     * Sort the list of modules using "sequence" key in meta-information
     *
     * @param array $origList
     * @return array
     */
    private function sortBySequence($origList)
    {
        $nodes = [];
        $relations = [];

        // collect graph nodes and edges
        foreach ($origList as $module) {
            $nodes[] = $module['name'];
            foreach ($module['sequence'] as $dependency) {
                $relations[] = [$module['name'], $dependency];
            }
        }

        // construct a graph and perform topological sort on it
        $moduleGraph = new \Magento\Framework\Data\Graph($nodes, $relations);
        $sortedResult = $moduleGraph->topoSort();

        // re-attach version information to the linear ordering
        $result = [];
        foreach ($sortedResult as $item) {
            $result[$item] = $origList[$item];
        }

        return $result;
    }
}
