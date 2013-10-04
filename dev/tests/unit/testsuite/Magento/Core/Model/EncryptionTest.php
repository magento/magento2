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

namespace Magento\Core\Model;

class EncryptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider setHelperGetHashDataProvider
     */
    public function testSetHelperGetHash($input)
    {
        $helper = $this->getMockBuilder('Magento\Core\Helper\Data')
                      ->disableOriginalConstructor()
                      ->setMockClassName('Magento_Core_Helper_Data_Stub')
                      ->getMock();
        $objectManager = $this->getMock('Magento\ObjectManager');
        $objectManager->expects($this->once())
            ->method('get')
            ->with($this->stringContains('Magento_Core_Helper_Data_Stub'))
            ->will($this->returnValue($helper));
        $coreConfig = $this->getMock('Magento\Core\Model\Config', array(), array(), '', false);

        /**
         * @var \Magento\Core\Model\Encryption
         */
        $model = new \Magento\Core\Model\Encryption($objectManager, $coreConfig, 'cryptKey');
        $model->setHelper($input);
        $model->getHash('password', 1);
    }

    /**
     * @return array
     */
    public function setHelperGetHashDataProvider()
    {
        $helper = $this->getMockBuilder('Magento\Core\Helper\Data')
                      ->disableOriginalConstructor()
                      ->setMockClassName('Magento_Core_Helper_Data_Stub')
                      ->getMock();
        return array(
            'string' => array('Magento_Core_Helper_Data_Stub'),
            'object' => array($helper),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetHelperException()
    {
        $objectManager = $this->getMock('Magento\ObjectManager');
        $coreConfig = $this->getMock('Magento\Core\Model\Config', array(), array(), '', false);

        /**
         * @var \Magento\Core\Model\Encryption
         */
        $model = new \Magento\Core\Model\Encryption($objectManager, $coreConfig);
        /** Mock object is not instance of \Magento\Code\Helper\Data and should not pass validation */
        $input = $this->getMock('Magento\Code\Helper\Data', array(), array(), '', false);
        $model->setHelper($input);
    }
}
