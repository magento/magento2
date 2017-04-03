<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source;


use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for creation of multiple products
 *
 */
class FileParserFactory
{
    /**
     * File parser creation map
     *
     * @var string[][]
     */
    private $parserMap = [];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;


    public function __construct(ObjectManagerInterface $objectManager, array $parserMap)
    {
        $this->objectManager = $objectManager;
        foreach ($parserMap as $item) {
            $this->addParserMap($item);
        }
    }

    /**
     * Creates an instance of file parser from file path
     *
     * @param string $filePath
     * @param array $options
     *
     * @return FileParserInterface
     */
    public function create($filePath, $options = [])
    {
        $extension = $this->extractFileExtension($filePath);

        $this->assertFileExtensionIsSupported($filePath, $extension);

        $parserInfo = $this->parserMap[$extension];

        $arguments = [
            $parserInfo['argument'] => $filePath
        ];

        $arguments += $options;

        return $this->objectManager->create(
            $parserInfo['class'],
            $arguments
        );
    }

    private function addParserMap($parserMap)
    {
        $this->assertParserMap($parserMap);
        $this->parserMap[$parserMap['extension']] = [
            'class' => $parserMap['class'],
            'argument' => $parserMap['argument']
        ];
    }

    private function extractFileExtension($filePath)
    {
        return substr($filePath, strrpos($filePath, '.') + 1, strlen($filePath));
    }

    private function assertParserMap($mapItem)
    {
        if (empty($mapItem['extension'])) {
            throw new \InvalidArgumentException('Missing extension in parser definition');
        }
        if (empty($mapItem['class'])) {
            throw new \InvalidArgumentException('Missing class in parser definition');
        }
        if (empty($mapItem['argument'])) {
            throw new \InvalidArgumentException('Missing argument in parser definition');
        }
    }

    private function assertFileExtensionIsSupported($filePath, $extension)
    {
        if (!isset($this->parserMap[$extension])) {
            throw new \InvalidArgumentException(
                sprintf('File "%s" is an invalid format', $filePath)
            );
        }
    }
}
