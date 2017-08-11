<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source;


use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Model\Import\Source\FileParser\ParserFactoryInterface;
use Magento\ImportExport\Model\Import\Source\FileParser\ParserInterface;

class FileFactory
{
    /** @var ParserFactoryInterface */
    private $parserFactory;

    /** @var ObjectManagerInterface */
    private $objectManager;

    public function __construct(ParserFactoryInterface $parserFactory, ObjectManagerInterface $objectManager)
    {
        $this->parserFactory = $parserFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * Creates file source from file path
     *
     * @param string $path
     * @param array $options
     * @return AbstractSource
     */
    public function createFromFilePath($path, array $options = [])
    {
        return $this->createFromFileParser(
            $this->parserFactory->create($path, $options)
        );
    }

    /**
     * Creates file source from file parser
     *
     * @param ParserInterface $parser
     * @return AbstractSource
     */
    public function createFromFileParser(ParserInterface $parser)
    {
        return $this->objectManager->create(File::class, ['parser' => $parser]);
    }
}
