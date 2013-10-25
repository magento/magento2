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
namespace Magento\Core\Model\Config\FileResolver;

class PrimaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\FileResolver\Primary
     */
    protected $_model;

    protected function setUp()
    {
        $appConfigDir = __DIR__ . DIRECTORY_SEPARATOR
            . '_files' . DIRECTORY_SEPARATOR . 'primary'
            . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'etc';

        $applicationDirsMock = $this->getMock('Magento\App\Dir', array(), array('getDir'), '', false);
        $applicationDirsMock->expects($this->any())
            ->method('getDir')
            ->with(\Magento\App\Dir::CONFIG)
            ->will($this->returnValue($appConfigDir));

        $this->_model = new \Magento\Core\Model\Config\FileResolver\Primary($applicationDirsMock);
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
        $appConfigDir = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'primary'
            . DIRECTORY_SEPARATOR .  'app' . DIRECTORY_SEPARATOR . 'etc';

        return array(
            array(
                array(
                    $appConfigDir . DIRECTORY_SEPARATOR . 'di.xml',
                    $appConfigDir . DIRECTORY_SEPARATOR . 'some_config' .DIRECTORY_SEPARATOR.  'di.xml',
                ),
                'primary',
                'di.xml',
            )
        );
    }
}
