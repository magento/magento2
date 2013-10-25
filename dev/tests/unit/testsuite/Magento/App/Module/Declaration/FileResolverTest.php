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
namespace Magento\App\Module\Declaration;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\Module\Declaration\FileResolver
     */
    protected $_model;

    protected function setUp()
    {
        $baseDir = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/FileResolver/_files');

        $applicationDirs = $this->getMock('Magento\App\Dir', array(), array('getDir'), '', false);
        $applicationDirs->expects($this->any())
            ->method('getDir')
            ->will($this->returnValueMap(array(
                array(
                    \Magento\App\Dir::CONFIG,
                    $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR .'etc',
                ),
                array(
                    \Magento\App\Dir::MODULES,
                    $baseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR .'code',
                ),
            )));
        $this->_model = new \Magento\App\Module\Declaration\FileResolver($applicationDirs);
    }

    public function testGet()
    {
        $expectedResult = array(
            __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/FileResolver/_files/app/code/Module/Four/etc/module.xml'),
            __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/FileResolver/_files/app/code/Module/One/etc/module.xml'),
            __DIR__ . str_replace(
                '/', DIRECTORY_SEPARATOR, '/FileResolver/_files/app/code/Module/Three/etc/module.xml'
            ),
            __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/FileResolver/_files/app/code/Module/Two/etc/module.xml'),
            __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/FileResolver/_files/app/etc/custom/module.xml'),
        );
        $this->assertEquals($expectedResult, $this->_model->get('module.xml', 'global'));
    }

}
