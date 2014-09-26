<?php
/**
 * Filesystem configuration loader. Loads configuration from XML files
 *
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */

namespace Magento\Config\Reader;

use Magento\Config\FileResolverInterface;
use Magento\Config\Converter\ConverterInterface;
use Magento\Config\SchemaLocatorInterface;

class Filesystem implements ReaderInterface
{
    /**
     * File locator
     *
     * @var \Magento\Config\FileResolverInterface
     */
    protected $fileResolver;

    /**
     * Config converter
     *
     * @var \Magento\Config\Converter\ConverterInterface
     */
    protected $converter;

    /**
     * Path to corresponding XSD file with validation rules
     *
     * @var string
     */
    protected $schemaFile;

    /**
     * The name of file that stores configuration
     *
     * @var string
     */
    protected $fileName;

    /**
     * Class of dom configuration document used for merge
     *
     * @var string
     */
    protected $domDocumentClass;

    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $idAttributes = array();

    /**
     * @param FileResolverInterface $fileResolver
     * @param ConverterInterface $converter
     * @param SchemaLocatorInterface $schemaLocator
     * @param string $fileName
     * @param string $domDocumentClass
     * @param array $idAttributes
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        ConverterInterface $converter,
        SchemaLocatorInterface $schemaLocator,
        $fileName,
        $domDocumentClass = '\Magento\Framework\Config\Dom',
        $idAttributes = array()
    ) {
        $this->fileResolver = $fileResolver;
        $this->converter = $converter;
        $this->fileName = $fileName;
        $this->idAttributes = array_replace($this->idAttributes, $idAttributes);
        $this->schemaFile = $schemaLocator->getSchema();
        $this->domDocumentClass = $domDocumentClass;
    }

    /**
     * Load configuration
     *
     * @return array
     */
    public function read()
    {
        $fileList = $this->fileResolver->get($this->fileName);
        if (!count($fileList)) {
            return array();
        }
        return $this->readFiles($fileList);
    }

    /**
     * Read configuration files
     *
     * @param array $fileList
     * @return array
     * @throws \Exception
     */
    protected function readFiles($fileList)
    {
        /** @var \Magento\Framework\Config\Dom $configMerger */
        $configMerger = null;
        foreach ($fileList as $key => $content) {
            try {
                if (!$configMerger) {
                    $configMerger = $this->createConfigMerger($this->domDocumentClass, $content);
                } else {
                    $configMerger->merge($content);
                }
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                throw new \Exception("Invalid XML in file " . $key . ":\n" . $e->getMessage());
            }
        }

        $errors = array();
        if ($configMerger && !$configMerger->validate($this->schemaFile, $errors)) {
            $message = "Invalid Document \n";
            throw new \Exception($message . implode("\n", $errors));
        }

        $output = array();
        if ($configMerger) {
            $output = $this->converter->convert($configMerger->getDom());
        }
        return $output;
    }

    /**
     * Return newly created instance of a config merger
     *
     * @param string $mergerClass
     * @param string $initialContents
     * @return \Magento\Framework\Config\Dom
     * @throws \UnexpectedValueException
     */
    protected function createConfigMerger($mergerClass, $initialContents)
    {
        $result = new $mergerClass($initialContents, $this->idAttributes, null, $this->schemaFile);
        if (!$result instanceof \Magento\Framework\Config\Dom) {
            throw new \UnexpectedValueException(
                "Instance of the DOM config merger is expected, got {$mergerClass} instead."
            );
        }
        return $result;
    }
}
