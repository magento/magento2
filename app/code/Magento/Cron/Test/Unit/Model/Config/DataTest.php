<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Model\Config;

use Magento\Cron\Model\Config\Data;
use Magento\Cron\Model\Config\Reader\Db;
use Magento\Cron\Model\Config\Reader\Xml;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * Testing return jobs from different sources (DB, XML)
     */
    public function testGetJobs()
    {
        $reader = $this->getMockBuilder(
            Xml::class
        )->disableOriginalConstructor()
            ->getMock();
        $cache = $this->getMockForAbstractClass(CacheInterface::class);
        $dbReader = $this->getMockBuilder(
            Db::class
        )->disableOriginalConstructor()
            ->getMock();

        $jobs = [
            'job1' => ['schedule' => '1 1 1 1 1', 'instance' => 'JobModel1_1', 'method' => 'method1_1'],
            'job3' => ['schedule' => '3 3 3 3 3', 'instance' => 'JobModel3', 'method' => 'method3'],
        ];

        $dbReaderData = [
            'job1' => ['schedule' => '* * * * *', 'instance' => 'JobModel1', 'method' => 'method1'],
            'job2' => ['schedule' => '* * * * *', 'instance' => 'JobModel2', 'method' => 'method2'],
        ];

        $cache->expects($this->any())
            ->method('load')
            ->with('test_cache_id')
            ->willReturn(json_encode($jobs));

        $dbReader->expects($this->once())->method('get')->willReturn($dbReaderData);

        $serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $serializerMock->method('unserialize')
            ->willReturn($jobs);

        $configData = new Data($reader, $cache, $dbReader, 'test_cache_id', $serializerMock);

        $expected = [
            'job1' => ['schedule' => '* * * * *', 'instance' => 'JobModel1', 'method' => 'method1'],
            'job2' => ['schedule' => '* * * * *', 'instance' => 'JobModel2', 'method' => 'method2'],
            'job3' => ['schedule' => '3 3 3 3 3', 'instance' => 'JobModel3', 'method' => 'method3'],
        ];

        $result = $configData->getJobs();
        $this->assertEquals($expected, $result);
    }
}
