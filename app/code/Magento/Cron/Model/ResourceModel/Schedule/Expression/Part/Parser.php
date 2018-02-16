<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;

/**
 * Cron expression part parser
 *
 * @api
 */
class Parser implements ParserInterface
{
    /**
     * @var ParserFactory
     */
    private $parserFactory;

    /**
     * Parser constructor.
     *
     * @param ParserFactory $parserFactory
     */
    public function __construct(
        ParserFactory $parserFactory
    ) {
        $this->parserFactory = $parserFactory;
    }

    /**
     * Perform parse of cron expression part
     *
     * @param string $partValue
     * @param string $parserType
     *
     * @return bool|array
     */
    public function parse($partValue, $parserType)
    {
        return $this->parserFactory->create($parserType)->parse($partValue);
    }
}
