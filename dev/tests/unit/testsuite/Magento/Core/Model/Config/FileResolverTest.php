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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Config;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\FileResolver
     */
    protected $_model;

    protected function setUp()
    {
        $appConfigDir = __DIR__ . DIRECTORY_SEPARATOR . 'FileResolver'
            . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'etc';

        $applicationDirs = $this->getMock('Magento\App\Dir', array(), array('getDir'), '', false);
        $applicationDirs->expects($this->any())
            ->method('getDir')
            ->with(\Magento\App\Dir::CONFIG)
            ->will($this->returnValue($appConfigDir));

        $moduleReader = $this->getMock('Magento\Core\Model\Config\Modules\Reader', array(),
            array('getConfigurationFiles'), '', false);
        $moduleReader->expects($this->any())
            ->method('getConfigurationFiles')
            ->will($this->returnValueMap(
                array(
                    array(
                        'adminhtml' . DIRECTORY_SEPARATOR . 'di.xml',
                        array(
                            'app/code/Custom/FirstModule/adminhtml/di.xml',
                            'app/code/Custom/SecondModule/adminhtml/di.xml',
                        )
                    ),
                    array(
                        'frontend' . DIRECTORY_SEPARATOR . 'di.xml',
                        array(
                            'app/code/Custom/FirstModule/frontend/di.xml',
                            'app/code/Custom/SecondModule/frontend/di.xml',
                        )
                    ),
                    array(
                        'di.xml',
                        array(
                            'app/code/Custom/FirstModule/di.xml',
                            'app/code/Custom/SecondModule/di.xml',
                        )
                    ),
                )
            ));
        $this->_model = new \Magento\Core\Model\Config\FileResolver($moduleReader, $applicationDirs);
    }

    /**
     * @param array $expectedResult
     * @param string $scope
     * @param string $filename
     * @dataProvider getMethodDataProvider
     */
    public function testGet(array $expectedResult, $scope, $filename)
    {
        $this->assertEquals($expectedResult, $this->_model->get($filename, $scope));
    }

    /**
     * @return array
     */
    public function getMethodDataProvider()
    {
        $appConfigDir = __DIR__ . DIRECTORY_SEPARATOR . 'FileResolver'
            . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'etc';
        return array(
            array(
                array(
                    $appConfigDir . DIRECTORY_SEPARATOR . 'config.xml',
                    $appConfigDir . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . 'config.xml',
                ),
                'primary',
                'config.xml',
            ),
            array(
                array(
                    'app/code/Custom/FirstModule/di.xml',
                    'app/code/Custom/SecondModule/di.xml',
                ),
                'global',
                'di.xml',
            ),
            array(
                array(
                    'app/code/Custom/FirstModule/frontend/di.xml',
                    'app/code/Custom/SecondModule/frontend/di.xml',
                ),
                'frontend',
                'di.xml',
            ),
            array(
                array(
                    'app/code/Custom/FirstModule/adminhtml/di.xml',
                    'app/code/Custom/SecondModule/adminhtml/di.xml',
                ),
                'adminhtml',
                'di.xml',
            ),
        );
    }
}
