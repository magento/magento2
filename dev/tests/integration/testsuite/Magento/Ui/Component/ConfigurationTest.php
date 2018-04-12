<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentFile;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Ui\Config\Reader\DefinitionMap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DirSearch
     */
    private $dirSearch;

    /**
     * @var ReadInterface
     */
    private $appDir;

    /**
     * @var ReadInterface
     */
    private $rootDir;

    /**
     * @var \DOMDocument
     */
    private $dom;

    /**
     * @var array
     */
    private $map;

    /**
     * @var string
     */
    private $currentComponent;

    /**
     * @var ComponentFile
     */
    private $currentFile;

    /**
     * @var array
     */
    private $whiteList = [
        'argument[@name="data"]/item[@name="config"]/item[@name="multiple"]' => [
            '//*[@formElement="select"]',
            '//*[substring(@component, string-length(@component) - string-length("ui-group") +1) = "ui-group"]'
        ]
    ];

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $mapReader = $objectManager->create(DefinitionMap::class);
        $this->map = $mapReader->read();

        $this->dirSearch = $objectManager->create(DirSearch::class);

        /** @var Filesystem $filesystem */
        $filesystem = $objectManager->create(Filesystem::class);
        $this->appDir = $filesystem->getDirectoryRead(DirectoryList::APP);
        $this->rootDir = $filesystem->getDirectoryRead(DirectoryList::ROOT);
    }

    /**
     * @return void
     */
    public function testConfiguration()
    {
        $uiConfigurationFiles = $this->dirSearch->collectFilesWithContext(
            ComponentRegistrar::MODULE,
            'view/*/ui_component/*.xml'
        );
        $this->generateXpaths();

        $result = [];
        /** @var ComponentFile $file */
        foreach ($uiConfigurationFiles as $file) {
            $this->currentFile = $file;
            $fullPath = $file->getFullPath();
            // by default search files in `app` directory but Magento can be installed via composer
            // or some modules can be in `vendor` directory (like bundled extensions)
            try {
                $content = $this->appDir->readFile($this->appDir->getRelativePath($fullPath));
            } catch (FileSystemException $e) {
                $content = $this->rootDir->readFile($this->rootDir->getRelativePath($fullPath));
            }
            $this->assertConfigurationSemantic($this->getDom($content), $result);
        }
        if (!empty($result)) {
            $this->fail(implode("\n\n", $result));
        }
    }

    /**
     * @return void
     */
    private function generateXpaths()
    {
        foreach ($this->map as $name => &$map) {
            $this->currentComponent = $name;
            $xpaths = [];
            $counter = 0;
            while (!empty($map)) {
                $this->hasXpaths($map, $xpaths, $counter);
                $counter++;
            }
            $this->map[$name]['xpaths'] = $xpaths;
        }
    }

    /**
     * @param \DOMNode $node
     * @param array $result
     * @return void
     */
    private function assertConfigurationSemantic(\DOMNode $node, &$result = [])
    {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                if (isset($this->map[$child->localName])) {
                    $xpaths = [];
                    $this->currentComponent = $child->localName;
                    if (isset($this->map[$this->currentComponent]['xpaths'])) {
                        $xpaths = $this->map[$this->currentComponent]['xpaths'];
                    }

                    $domXpath = new \DOMXPath($this->getDom());
                    foreach ($xpaths as $xpathData) {
                        if ($domXpath->query($xpathData['xpath'], $child)->length !== 0
                            && !$this->isAvailable($xpathData['xpath'], $child)
                        ) {
                            $result[] = 'Xpath: "' . $xpathData['xpath'] . '" is a forbidden.' . "\n" .
                                'This node should migrate to "' . trim($xpathData['target']) . "\"\n" .
                                'File: ' . $this->currentFile->getFullPath() . "\n";
                        }
                    }
                }
                $this->assertConfigurationSemantic($child, $result);
            }
        }
    }

    /**
     * @param string $targetXpath
     * @param \DOMElement $node
     * @return bool
     */
    private function isAvailable($targetXpath, \DOMElement $node)
    {
        $domXpath = new \DOMXPath($this->getDom());
        if (isset($this->whiteList[$targetXpath])) {
            $availableForXpath = $this->whiteList[$targetXpath];
            foreach ($availableForXpath as $xpath) {
                $result = $domXpath->query($xpath, $node);
                if ($result->length != 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param array $data
     * @param array $result
     * @param int $counter
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function hasXpaths(array &$data, array &$result, $counter)
    {

        foreach ($data as $name => &$child) {
            if (!is_array($child)) {
                continue;
            }
            if (isset($child['value'])) {
                $result[$counter]['xpath'] .= '[@name="' . $child['name'] . '"]';
                $result[$counter]['target'] = $child['value'];
                unset($data[$name]);
                $this->deleteEmptyNodes($this->map[$this->currentComponent]);
                return true;
            }

            if (isset($child['name']) && is_string($child['name'])) {
                $result[$counter]['xpath'] .= '[@name="' . $child['name'] . '"]';
                $break = false;
                if (isset($child['item'])) {
                    $result[$counter]['xpath'] .= '/item';
                    $break = $this->hasXpaths($child['item'], $result, $counter);
                } elseif (isset($child['argument'])) {
                    $result[$counter]['xpath'] .= '/argument';
                    $break = $this->hasXpaths($child['argument'], $result, $counter);
                }
                if ($break) {
                    return true;
                }
            }

            if (!isset($result[$counter]['xpath'])) {
                $result[$counter]['xpath'] = '';
            }
            $result[$counter]['xpath'] .= $name;
            $this->hasXpaths($child, $result, $counter);
        }
        return true;
    }

    /**
     * @param array $map
     * @param bool $isRemoveParentNode
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function deleteEmptyNodes(array &$map, $isRemoveParentNode = false)
    {
        if (empty($map)) {
            return true;
        }
        foreach ($map as $name => &$child) {
            if (is_array($child)) {
                $isRemoveParentNode = $this->deleteEmptyNodes($map[$name]);
            }
            if (empty($map[$name]) || $isRemoveParentNode) {
                if ((isset($map['item']) && empty($map['item']))
                    || (isset($map['argument']) && empty($map['argument']))
                ) {
                    unset($map[$name]);
                    return true;
                }
                unset($map[$name]);
            }
        }
        return false;
    }

    /**
     * @param string|null $content
     * @return \DOMDocument
     */
    private function getDom($content = null)
    {
        if ($content) {
            $this->dom = new \DOMDocument();
            $this->dom->loadXML($content);
        }
        return $this->dom;
    }
}
