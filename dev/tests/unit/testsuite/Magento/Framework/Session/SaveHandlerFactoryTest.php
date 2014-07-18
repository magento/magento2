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
namespace Magento\Framework\Session;

class SaveHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate($handlers, $saveClass, $saveMethod)
    {
        $saveHandler = $this->getMock($saveClass);
        $objectManager = $this->getMock(
            '\Magento\Framework\ObjectManager\ObjectManager',
            array('create'),
            array(),
            '',
            false
        );
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($saveClass),
            $this->equalTo(array())
        )->will(
            $this->returnValue($saveHandler)
        );
        $model = new SaveHandlerFactory($objectManager, $handlers);
        $result = $model->create($saveMethod);
        $this->assertInstanceOf($saveClass, $result);
        $this->assertInstanceOf('\Magento\Framework\Session\SaveHandler\Native', $result);
        $this->assertInstanceOf('\SessionHandler', $result);
    }

    /**
     * @return array
     */
    public static function createDataProvider()
    {
        return array(array(array(), 'Magento\Framework\Session\SaveHandler\Native', 'files'));
    }
}
