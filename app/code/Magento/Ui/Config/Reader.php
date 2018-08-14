<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config;

use Magento\Framework\Config\ConverterInterface as ConfigConverter;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * UI Component configuration reader
 */
class Reader implements ReaderInterface
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    private $idAttributes = ['/' => 'name'];

    /**
     * @var Reader\Definition
     */
    private $definitionReader;

    /**
     * @var ReaderFactory
     */
    private $readerFactory;

    /**
     * @var FileResolverInterface
     */
    private $fileResolver;

    /**
     * @var ConfigConverter
     */
    private $converter;

    /**
     * @var Reader\DomFactory
     */
    private $readerDomFactory;

    /**
     * The name of file that stores Ui configuration
     *
     * @var string
     */
    private $fileName;

    /**
     * Reader constructor.
     *
     * @param string $fileName
     * @param FileResolverInterface $fileResolver
     * @param ConfigConverter $converter
     * @param Reader\Definition $definitionReader
     * @param ReaderFactory $readerFactory
     * @param Reader\DomFactory $readerDomFactory
     * @param array $idAttributes
     */
    public function __construct(
        $fileName,
        FileResolverInterface $fileResolver,
        ConfigConverter $converter,
        Reader\Definition $definitionReader,
        ReaderFactory $readerFactory,
        Reader\DomFactory $readerDomFactory,
        array $idAttributes = []
    ) {
        $this->fileName = $fileName;
        $this->fileResolver = $fileResolver;
        $this->converter = $converter;
        $this->definitionReader = $definitionReader;
        $this->readerFactory = $readerFactory;
        $this->readerDomFactory = $readerDomFactory;
        $this->idAttributes = array_replace($this->idAttributes, $idAttributes);
    }

    /**
     * Load configuration scope
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $scope = $scope ?: 'global';
        $fileList = $this->fileResolver->get($this->fileName, $scope);
        if (!count($fileList)) {
            return [];
        }
        $output = $this->readFiles($fileList);

        return $output;
    }

    /**
     * Read, merge configuration files and validate resulted configuration
     *
     * @param array $fileList
     * @return array
     * @throws LocalizedException
     */
    private function readFiles($fileList)
    {
        /** @var \Magento\Ui\Config\Reader\Dom $configMerger */
        $configMerger = null;
        $output = [];
        foreach ($fileList as $key => $content) {
            try {
                $configMerger = $this->readerDomFactory->create(
                    [
                        'xml' => $content,
                        'idAttributes' => $this->idAttributes,
                    ]
                );
                $output = array_replace_recursive($output, $this->converter->convert($configMerger->getDom()));
            } catch (ValidationException $e) {
                throw new LocalizedException(new Phrase("Invalid XML in file %1:\n%2", [$key, $e->getMessage()]));
            }
        }

        $definitionData = $this->definitionReader->read();

        if (isset($output['attributes']['extends'])) {
            $extendsReader = $this->readerFactory->create(
                [
                    'fileName' => sprintf(
                        Data::SEARCH_PATTERN,
                        $output['attributes']['extends']
                    )
                ]
            );
            $extendsData = $extendsReader->read();
            $output = array_replace_recursive($extendsData, $output);
        }

        $output = $this->mergeDefinition($output, $definitionData);

        return $output;
    }

    /**
     * Merge definition to ui component configuration
     *
     * @param array $component
     * @param array $definitions
     * @return array
     */
    private function mergeDefinition(array $component, array $definitions)
    {
        foreach ($component['children'] as $name => $child) {
            $component['children'][$name] = $this->mergeDefinition($child, $definitions);
        }
        if (isset($component['uiComponentType'])) {
            $definition = isset($definitions[$component['uiComponentType']])
                ? $definitions[$component['uiComponentType']]
                : [];
            $component = array_replace_recursive($definition, $component);
            unset($component['uiComponentType']);
        }

        return $component;
    }
}
