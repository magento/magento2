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
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\EntryPoint;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testProcessRequestBootstrapException()
    {
        if (!\Magento\TestFramework\Helper\Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test entry point response without sending headers');
        }

        $config = $this->getMock('Magento\Core\Model\Config\Primary', array(), array(), '', false);
        $objectManager = $this->getMock('Magento\ObjectManager');
        $objectManager->expects($this->any())
            ->method('get')
            ->will($this->throwException(new \Magento\BootstrapException('exception_message')));

        $config = $this->getMock('Magento\Core\Model\Config\Primary', array(), array(), '', false);


        /** @var \Magento\Core\Model\EntryPoint\Http $model */
        $model = new \Magento\Core\Model\EntryPoint\Http($config, $objectManager);
        ob_start();
        $model->processRequest();
        $content = ob_get_clean();

        $headers = xdebug_get_headers();
        $this->assertContains('Content-Type: text/plain', $headers);
        $this->assertEquals('exception_message', $content, 'The response must contain exception message, and only it');
    }
}
