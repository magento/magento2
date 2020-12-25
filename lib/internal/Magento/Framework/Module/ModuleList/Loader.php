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
        $excludeSet = array_flip($exclude);

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
            if (!isset($excludeSet[$name])) {
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
     * @throws \Magento\Framework\Exception\FileSystemException
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
     * @throws \Exception
     */
    private function sortBySequence(array $origList): array
    {
        ksort($origList);
        $missingRel = [];

        foreach ($origList as $name => &$data) {
            if (!isset($data['sequence'])) $data['sequence'] = [];
            $data['_cnt'] = 0;
            $data['_im'] = (strpos($name, 'Magento_') !== 0) ? 1 : 0;
            $data['_rel'] = [];
        }

        foreach ($origList as $name => &$data) {
            foreach ($data['sequence'] as $rel) {
                if (!array_key_exists($rel, $origList)) {
                    $origList[$rel] = [
                        'sequence' => [],
                        '_rel' => [],
                        '_cnt' => 0,
                        '_im' => (strpos($rel, 'Magento_') !== 0) ? 1 : 0
                    ];
                    $missingRel[] = $rel;
                }
                $origList[$rel]['_rel'][$name] = &$data;
                $data['_cnt']++;
            }
        }
        // sort so we see have
        uasort($origList, function (&$a, &$b) {
            if ($a['_im'] == $b['_im']) {
                return ($a['_cnt'] > $b['_cnt']) ? 1 : 0;
            } else
                if ($a['_im'] == 1) {
                    return 1;
                } else {
                    return -1;
                }
        });

        $result = [];
        $lastTime = count($origList) + 1;

        // Collect Magento modules
        while ($lastTime > count($origList)) {
            $lastTime = count($origList);
            foreach ($origList as $name => &$data) {
                if ($data['_im'] == 1) break 2;

                if ($data['_cnt'] == 0) {
                    $this->_follow($result,$origList, $data, $name);
                }
            }
        }
        // collect others.
        $lastTime = count($origList) + 1;
        while ($lastTime > count($origList)) {
            $lastTime = count($origList);
            foreach ($origList as $name => &$data) {
                if ($data['_cnt'] == 0) {
                    $this->_follow($result,$origList, $data, $name);
                }
            }
        }

        if (count($origList) > 0) {
            $badModules = join(', ', array_keys($origList));
            throw new \LogicException("Circular reference for modules: $badModules.");
        }
        $result = array_diff_key($result, array_flip($missingRel));
        // cleanup
        foreach ($result as $name => &$data) {
            unset($data['_rel'], $data['_im'], $data['_cnt']);
        }
        return $result;
    }

    /**
     * Follow chain of modules which dependencies were satisfies.
     * Altering $result and $origList directly.
     *
     * @param array $result
     * @param array $origList
     * @param array $data
     * @param string $current
     */
    private function _follow(array &$result, array &$origList, array &$data, string $current) {
        $result[$current] = $data;
        unset($origList[$current]);
        foreach ($data['_rel'] as $rname=>&$rel) {
            $rel['_cnt'] -= 1;
            if ($rel['_cnt'] == 0 && $data['_im'] == $rel['_im']) {
                $this->_follow($result,$origList,$rel, $rname);
            }
        };
    }
}
