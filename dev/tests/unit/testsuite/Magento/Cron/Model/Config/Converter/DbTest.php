<?php
/**
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
 * @category    Magento
 * @package     Magento_Cron
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cron\Model\Config\Converter;

class DbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cron\Model\Config\Converter\Db
     */
    protected $_converter;

    /**
     * Prepare parameters
     */
    protected function setUp()
    {
        $this->_converter = new \Magento\Cron\Model\Config\Converter\Db();
    }

    /**
     * Testing not existed list of jobs
     */
    public function testConvertNoJobs()
    {
        $source = array();
        $result = $this->_converter->convert($source);
        $this->assertEmpty($result);
    }

    /**
     * Testing parameters in 'schedule' container
     */
    public function testConvertConfigParams()
    {
        $fullJob = array(
            'schedule' => array(
                'config_path' => 'config/path',
                'cron_expr'   => '* * * * *'
            )
        );
        $nullJob = array(
            'schedule' => array(
                'config_path' => null,
                'cron_expr' => null
            )
        );
        $notFullJob = array(
            'schedule' => ''
        );
        $source = array(
            'crontab' => array(
                'jobs' => array(
                    'job_name_1' => $fullJob,
                    'job_name_2' => $nullJob,
                    'job_name_3' => $notFullJob,
                    'job_name_4' => array()
                )
            )
        );
        $expected = array(
            'job_name_1' => array('config_path' => 'config/path', 'schedule' => '* * * * *'),
            'job_name_2' => array('config_path' => null, 'schedule' => null),
            'job_name_3' => array('schedule' => ''),
            'job_name_4' => array(''),
        );

        $result = $this->_converter->convert($source);
        $this->assertEquals($expected['job_name_1']['config_path'], $result['job_name_1']['config_path']);
        $this->assertEquals($expected['job_name_1']['schedule'], $result['job_name_1']['schedule']);

        $this->assertEquals($expected['job_name_2']['config_path'], $result['job_name_2']['config_path']);
        $this->assertEquals($expected['job_name_2']['schedule'], $result['job_name_2']['schedule']);

        $this->assertArrayHasKey('schedule', $result['job_name_3']);
        $this->assertEmpty($result['job_name_3']['schedule']);

        $this->assertEmpty($result['job_name_4']);
    }

    /**
     * Testing 'run' container
     */
    public function testConvertRunConfig()
    {
        $runFullJob = array(
            'run' => array('model' => 'Model1::method1')
        );
        $runNoMethodJob = array(
            'run' => array('model' => 'Model2')
        );
        $runEmptyMethodJob = array(
            'run' => array('model' => 'Model3::')
        );
        $runNoModelJob = array(
            'run' => array('model' => '::method1')
        );

        $source = array(
            'crontab' => array(
                'jobs' => array(
                    'job_name_1' => $runFullJob,
                    'job_name_2' => $runNoMethodJob,
                    'job_name_3' => $runEmptyMethodJob,
                    'job_name_4' => $runNoModelJob,
                )
            )
        );
        $expected = array(
            'job_name_1' => array('instance' => 'Model1', 'method' => 'method1'),
            'job_name_2' => array(),
            'job_name_3' => array(),
            'job_name_4' => array()
        );
        $result = $this->_converter->convert($source);
        $this->assertEquals($expected['job_name_1']['instance'], $result['job_name_1']['instance']);
        $this->assertEquals($expected['job_name_1']['method'], $result['job_name_1']['method']);

        $this->assertEmpty($result['job_name_2']);
        $this->assertEmpty($result['job_name_3']);
        $this->assertEmpty($result['job_name_4']);
    }
}
