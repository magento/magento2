<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Parser\ParserInterface as PartParserInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CronException;

/**
 * Cron expression part parser
 *
 * @api
 */
class ParserFactory
{
    const LIST_PARSER = 'List';
    const MODULUS_PARSER = 'Modulus';
    const RANGE_PARSER = 'Range';

    /**
     * @return array
     */
    public function getAvailableParsers()
    {
        return [
            self::LIST_PARSER,
            self::MODULUS_PARSER,
            self::RANGE_PARSER,
        ];
    }

    /**
     * Get the parser specified by parser type
     *
     * @param string $parserType
     *
     * @throws CronException
     * @return PartParserInterface
     */
    public function create($parserType)
    {
        if (!in_array($parserType, $this->getAvailableParsers())) {
            throw new CronException(__('Invalid cron expression part parser type: %1', $parserType));
        }

        $parser = ObjectManager::getInstance()->get(
            'Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Parser\\' . $parserType . 'Parser'
        );

        if (!$parser instanceof PartParserInterface) {
            $exceptionMessage = 'Invalid cron expression part parser type: %1 is not an instance of '
                . PartParserInterface::class;
            throw new CronException(__($exceptionMessage, $parserType));
        }

        return $parser;
    }
}
