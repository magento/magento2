<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\ModuleList;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Declaration\Converter\Dom;
use Magento\Framework\Xml\Parser;

/**
 * Loader of module list information from the filesystem
 */
class Loader
{
    /**
     * Application filesystem
     *
     * @var Filesystem
     */
    private $filesystem;

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
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param Dom $converter
     * @param Parser $parser
     */
    public function __construct(Filesystem $filesystem, Dom $converter, Parser $parser)
    {
        $this->filesystem = $filesystem;
        $this->converter = $converter;
        $this->parser = $parser;
        $this->parser->initErrorHandler();
    }

    /**
     * Loads the full module list information
     *
     * @throws \Magento\Framework\Exception
     * @return array
     */
    public function load()
    {
        $result = [];
        $dir = $this->filesystem->getDirectoryRead(DirectoryList::MODULES);
        foreach ($dir->search('*/*/etc/module.xml') as $file) {
            $contents = $dir->readFile($file);

            try {
                $this->parser->loadXML($contents);
            } catch (\Magento\Framework\Exception $e) {
                throw new \Magento\Framework\Exception(
                    'Invalid Document: ' . $file . PHP_EOL . ' Error: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }

            $data = $this->converter->convert($this->parser->getDom());
            $name = key($data);
            $result[$name] = $data[$name];
        }
        return $this->sortBySequence($result);
    }

    /**
     * Sort the list of modules using "sequence" key in meta-information
     *
     * @param array $origList
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function sortBySequence($origList)
    {
        $expanded = [];
        foreach ($origList as $moduleName => $value) {
            $expanded[] = [
                'name' => $moduleName,
                'sequence' => $this->expandSequence($origList, $moduleName),
            ];
        }

        // Use "bubble sorting" because usort does not check each pair of elements and in this case it is important
        $total = count($expanded);
        for ($i = 0; $i < $total - 1; $i++) {
            for ($j = $i; $j < $total; $j++) {
                if (in_array($expanded[$j]['name'], $expanded[$i]['sequence'])) {
                    $temp = $expanded[$i];
                    $expanded[$i] = $expanded[$j];
                    $expanded[$j] = $temp;
                }
            }
        }

        $result = [];
        foreach ($expanded as $pair) {
            $result[$pair['name']] = $origList[$pair['name']];
        }

        return $result;
    }

    /**
     * Accumulate information about all transitive "sequence" references
     *
     * @param array $list
     * @param string $name
     * @param array $accumulated
     * @return array
     * @throws \Exception
     */
    private function expandSequence($list, $name, $accumulated = [])
    {
        $accumulated[] = $name;
        $result = $list[$name]['sequence'];
        foreach ($result as $relatedName) {
            if (in_array($relatedName, $accumulated)) {
                throw new \Exception("Circular sequence reference from '{$name}' to '{$relatedName}'.");
            }
            if (!isset($list[$relatedName])) {
                continue;
            }
            $relatedResult = $this->expandSequence($list, $relatedName, $accumulated);
            $result = array_unique(array_merge($result, $relatedResult));
        }
        return $result;
    }
}
