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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cron\Model\Config\Reader;

class DbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Config\Reader\DefaultReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_defaultReader;

    /**
     * @var \Magento\Cron\Model\Config\Converter\Db|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converter;

    /**
     * @var \Magento\Cron\Model\Config\Reader\Db
     */
    protected $_reader;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->_defaultReader = $this->getMockBuilder(
            'Magento\Store\Model\Config\Reader\DefaultReader'
        )->disableOriginalConstructor()->getMock();
        $this->_converter = new \Magento\Cron\Model\Config\Converter\Db();
        $this->_reader = new \Magento\Cron\Model\Config\Reader\Db($this->_defaultReader, $this->_converter);
    }

    /**
     * Testing method execution
     */
    public function testGet()
    {
        $job1 = array('schedule' => array('cron_expr' => '* * * * *'));
        $job2 = array('schedule' => array('cron_expr' => '1 1 1 1 1'));
        $data = array('crontab' => array('default' => array('jobs' => array('job1' => $job1, 'job2' => $job2))));
        $this->_defaultReader->expects($this->once())->method('read')->will($this->returnValue($data));
        $expected = array(
            'default' => array(
                'job1' => array('schedule' => $job1['schedule']['cron_expr']),
                'job2' => array('schedule' => $job2['schedule']['cron_expr'])
            )
        );

        $result = $this->_reader->get();
        $this->assertEquals($expected['default']['job1']['schedule'], $result['default']['job1']['schedule']);
        $this->assertEquals($expected['default']['job2']['schedule'], $result['default']['job2']['schedule']);
    }
}
