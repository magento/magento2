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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Module\ModuleList;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\Declaration\Converter\Dom;

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
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param Dom $converter
     */
    public function __construct(Filesystem $filesystem, Dom $converter)
    {
        $this->filesystem = $filesystem;
        $this->converter = $converter;
    }

    /**
     * Loads the full module list information
     *
     * @return array
     */
    public function load()
    {
        $result = [];
        $dir = $this->filesystem->getDirectoryRead(DirectoryList::MODULES);
        foreach ($dir->search('*/*/etc/module.xml') as $file) {
            $contents = $dir->readFile($file);
            $dom = new \DOMDocument();
            $dom->loadXML($contents);
            $data = $this->converter->convert($dom);
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
     */
    private function sortBySequence($origList)
    {
        $expanded = array();
        foreach ($origList as $moduleName => $value) {
            $expanded[] = array(
                'name' => $moduleName,
                'sequence' => $this->expandSequence($origList, $moduleName)
            );
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
