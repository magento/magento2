<?php
/**
 * Test case for \Magento\Framework\Profiler
 *
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
namespace Magento\Framework;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        \Magento\Framework\Profiler::reset();
    }

    /**
     * @dataProvider applyConfigDataProvider
     * @param array $config
     * @param array $expectedDrivers
     */
    public function testApplyConfigWithDrivers(array $config, array $expectedDrivers)
    {
        \Magento\Framework\Profiler::applyConfig($config, '');
        $this->assertAttributeEquals($expectedDrivers, '_drivers', 'Magento\Framework\Profiler');
    }

    /**
     * @return array
     */
    public function applyConfigDataProvider()
    {
        return array(
            'Empty config does not create any driver' => array('config' => array(), 'drivers' => array()),
            'Integer 0 does not create any driver' => array(
                'config' => array('drivers' => array(0)),
                'drivers' => array()
            ),
            'Integer 1 does creates standard driver' => array(
                'config' => array('drivers' => array(1)),
                'drivers' => array(new \Magento\Framework\Profiler\Driver\Standard())
            ),
            'Config array key sets driver type' => array(
                'configs' => array('drivers' => array('standard' => 1)),
                'drivers' => array(new \Magento\Framework\Profiler\Driver\Standard())
            ),
            'Config array key ignored when type set' => array(
                'config' => array('drivers' => array('custom' => array('type' => 'standard'))),
                'drivers' => array(new \Magento\Framework\Profiler\Driver\Standard())
            ),
            'Config with outputs element as integer 1 creates output' => array(
                'config' => array(
                    'drivers' => array(array('outputs' => array('html' => 1))),
                    'baseDir' => '/some/base/dir'
                ),
                'drivers' => array(
                    new \Magento\Framework\Profiler\Driver\Standard(
                        array('outputs' => array(array('type' => 'html', 'baseDir' => '/some/base/dir')))
                    )
                )
            ),
            'Config with outputs element as integer 0 does not create output' => array(
                'config' => array('drivers' => array(array('outputs' => array('html' => 0)))),
                'drivers' => array(new \Magento\Framework\Profiler\Driver\Standard())
            ),
            'Config with shortly defined outputs element' => array(
                'config' => array('drivers' => array(array('outputs' => array('foo' => 'html')))),
                'drivers' => array(
                    new \Magento\Framework\Profiler\Driver\Standard(array('outputs' => array(array('type' => 'html'))))
                )
            ),
            'Config with fully defined outputs element options' => array(
                'config' => array(
                    'drivers' => array(
                        array(
                            'outputs' => array(
                                'foo' => array(
                                    'type' => 'html',
                                    'filterName' => '/someFilter/',
                                    'thresholds' => array('someKey' => 123),
                                    'baseDir' => '/custom/dir'
                                )
                            )
                        )
                    )
                ),
                'drivers' => array(
                    new \Magento\Framework\Profiler\Driver\Standard(
                        array(
                            'outputs' => array(
                                array(
                                    'type' => 'html',
                                    'filterName' => '/someFilter/',
                                    'thresholds' => array('someKey' => 123),
                                    'baseDir' => '/custom/dir'
                                )
                            )
                        )
                    )
                )
            ),
            'Config with shortly defined output' => array(
                'config' => array('drivers' => array(array('output' => 'html'))),
                'drivers' => array(
                    new \Magento\Framework\Profiler\Driver\Standard(array('outputs' => array(array('type' => 'html'))))
                )
            )
        );
    }
}
