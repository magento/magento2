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
namespace Magento\Persistent\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_modulesReader;

    /**
     * @var  \Magento\Persistent\Helper\Data
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_modulesReader = $this->getMock('\Magento\Framework\Module\Dir\Reader', array(), array(), '', false);
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_helper = $objectManager->getObject(
            '\Magento\Persistent\Helper\Data',
            array('modulesReader' => $this->_modulesReader)
        );
    }

    public function testGetPersistentConfigFilePath()
    {
        $this->_modulesReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Persistent'
        )->will(
            $this->returnValue('path123')
        );
        $this->assertEquals('path123/persistent.xml', $this->_helper->getPersistentConfigFilePath());
    }
}
