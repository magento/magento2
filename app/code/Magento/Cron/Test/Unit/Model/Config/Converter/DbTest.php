<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Config\Converter;

class DbTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cron\Model\Config\Converter\Db
     */
    protected $_converter;

    /**
     * Prepare parameters
     */
    protected function setUp(): void
    {
        $this->_converter = new \Magento\Cron\Model\Config\Converter\Db();
    }

    /**
     * Testing not existed list of jobs
     */
    public function testConvertNoJobs()
    {
        $source = [];
        $result = $this->_converter->convert($source);
        $this->assertEmpty($result);
    }

    /**
     * Testing parameters in 'schedule' container
     */
    public function testConvertConfigParams()
    {
        $fullJob = ['schedule' => ['config_path' => 'config/path', 'cron_expr' => '* * * * *']];
        $nullJob = ['schedule' => ['config_path' => null, 'cron_expr' => null]];
        $notFullJob = ['schedule' => ''];
        $source = [
            'crontab' => [
                'default' => [
                    'jobs' => [
                        'job_name_1' => $fullJob,
                        'job_name_2' => $nullJob,
                        'job_name_3' => $notFullJob,
                        'job_name_4' => [],
                    ],
                ],
            ],
        ];
        $expected = [
            'default' => [
                'job_name_1' => ['config_path' => 'config/path', 'schedule' => '* * * * *'],
                'job_name_2' => ['config_path' => null, 'schedule' => null],
                'job_name_3' => ['schedule' => ''],
                'job_name_4' => [''],
            ],
        ];

        $result = $this->_converter->convert($source);
        $this->assertEquals(
            $expected['default']['job_name_1']['config_path'],
            $result['default']['job_name_1']['config_path']
        );
        $this->assertEquals(
            $expected['default']['job_name_1']['schedule'],
            $result['default']['job_name_1']['schedule']
        );

        $this->assertEquals(
            $expected['default']['job_name_2']['config_path'],
            $result['default']['job_name_2']['config_path']
        );
        $this->assertEquals(
            $expected['default']['job_name_2']['schedule'],
            $result['default']['job_name_2']['schedule']
        );

        $this->assertArrayHasKey('schedule', $result['default']['job_name_3']);
        $this->assertEmpty($result['default']['job_name_3']['schedule']);

        $this->assertEmpty($result['default']['job_name_4']);
    }

    /**
     * Testing 'run' container
     */
    public function testConvertRunConfig()
    {
        $runFullJob = ['run' => ['model' => 'Model1::method1']];
        $runNoMethodJob = ['run' => ['model' => 'Model2']];
        $runEmptyMethodJob = ['run' => ['model' => 'Model3::']];
        $runNoModelJob = ['run' => ['model' => '::method1']];

        $source = [
            'crontab' => [
                'default' => [
                    'jobs' => [
                        'job_name_1' => $runFullJob,
                        'job_name_2' => $runNoMethodJob,
                        'job_name_3' => $runEmptyMethodJob,
                        'job_name_4' => $runNoModelJob,
                    ],
                ],
            ],
        ];
        $expected = [
            'default' => [
                'job_name_1' => ['instance' => 'Model1', 'method' => 'method1'],
                'job_name_2' => [],
                'job_name_3' => [],
                'job_name_4' => [],
            ],
        ];
        $result = $this->_converter->convert($source);
        $this->assertEquals(
            $expected['default']['job_name_1']['instance'],
            $result['default']['job_name_1']['instance']
        );
        $this->assertEquals($expected['default']['job_name_1']['method'], $result['default']['job_name_1']['method']);

        $this->assertEmpty($result['default']['job_name_2']);
        $this->assertEmpty($result['default']['job_name_3']);
        $this->assertEmpty($result['default']['job_name_4']);
    }
}
