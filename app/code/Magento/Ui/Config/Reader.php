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
use Magento\Ui\Config\Reader\Dom as ReaderDom;

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
        private $fileName,
        private readonly FileResolverInterface $fileResolver,
        private readonly ConfigConverter $converter,
        private readonly Reader\Definition $definitionReader,
        private readonly ReaderFactory $readerFactory,
        private readonly Reader\DomFactory $readerDomFactory,
        array $idAttributes = []
    ) {
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
        /** @var ReaderDom $configMerger */
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
                throw new LocalizedException(
                    new Phrase(
                        'The XML in file "%1" is invalid:' . "\n%2\nVerify the XML and try again.",
                        [$key, $e->getMessage()]
                    )
                );
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
