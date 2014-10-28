<?php
/**
 * \Magento\Payment\Model\Config
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
namespace Magento\Payment\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $_model = null;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $cache \Magento\Framework\App\Cache */
        $cache = $objectManager->create('Magento\Framework\App\Cache');
        $cache->clean();
        $fileResolverMock = $this->getMockBuilder(
            'Magento\Framework\Config\FileResolverInterface'
        )->disableOriginalConstructor()->getMock();
        $fileList = array(
            file_get_contents(__DIR__ . '/_files/payment.xml'),
            file_get_contents(__DIR__ . '/_files/payment2.xml')
        );
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($fileList));
        $reader = $objectManager->create(
            'Magento\Payment\Model\Config\Reader',
            array('fileResolver' => $fileResolverMock)
        );
        $data = $objectManager->create('Magento\Payment\Model\Config\Data', array('reader' => $reader));
        $this->_model = $objectManager->create('Magento\Payment\Model\Config', array('dataStorage' => $data));
    }

    public function testGetCcTypes()
    {
        $expected = array('AE' => 'American Express', 'SM' => 'Switch/Maestro', 'SO' => 'Solo');
        $ccTypes = $this->_model->getCcTypes();
        $this->assertEquals($expected, $ccTypes);
    }

    public function testGetGroups()
    {
        $expected = array('any_payment' => 'Any Payment Methods', 'offline' => 'Offline Payment Methods');
        $groups = $this->_model->getGroups();
        $this->assertEquals($expected, $groups);
    }

    protected function tearDown()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $cache \Magento\Framework\App\Cache */
        $cache = $objectManager->create('Magento\Framework\App\Cache');
        $cache->clean();
    }
}
