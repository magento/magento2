<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Config\Reader\Env\Converter;

use Magento\Framework\MessageQueue\Config\Reader\Env\Converter\Publisher as EnvPublisherConverter;
use Magento\Framework\MessageQueue\Config\Reader\Env as ReaderEnv;

class PublisherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EnvPublisherConverter
     */
    private $converter;

    protected function setUp()
    {
        $connectionToExchangeMap =  [
            'amqp' => 'magento',
            'db'=> 'magento-db'
        ];
        $this->converter = new EnvPublisherConverter(
            $connectionToExchangeMap
        );
    }

    public function testConvert()
    {
        $source = include __DIR__ . '/../../../../_files/env_2_2.php';
        $expectedConfig = [
            'amqp-magento' => [
                'name' => 'amqp-magento',
                'exchange' => 'magento-db',
                'connection' =>'db'
            ]
        ];
        $actualResult = $this->converter->convert($source['config']);
        $this->assertEquals($expectedConfig, $actualResult[ReaderEnv::ENV_PUBLISHERS]);
    }

    public function testConvertIfPublisherConfigNotExist()
    {
        $source = include __DIR__ . '/../../../../_files/env_2_2.php';
        unset($source['config'][ReaderEnv::ENV_PUBLISHERS]);
        $actualResult = $this->converter->convert($source['config']);
        $this->assertEquals($source['config'], $actualResult);
    }
    public function testConvertIfConnectionConfigNotExist()
    {
        $source = include __DIR__ . '/../../../../_files/env_2_2.php';
        $topicName = 'inventory.counter.updated';
        unset($source['config'][ReaderEnv::ENV_PUBLISHERS][$topicName]['connections']);
        $actualResult = $this->converter->convert($source['config']);
        $expectedResult[ReaderEnv::ENV_CONSUMERS] = $source['config'][ReaderEnv::ENV_CONSUMERS];
        $expectedResult[ReaderEnv::ENV_PUBLISHERS] = [];
        $this->assertEquals($expectedResult, $actualResult);
    }
}
