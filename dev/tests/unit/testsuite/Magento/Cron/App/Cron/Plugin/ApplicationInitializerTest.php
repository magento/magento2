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
namespace Magento\Cron\App\Cron\Plugin;

class ApplicationInitializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cron\App\Cron\Plugin\ApplicationInitializer
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_applicationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sidResolverMock;

    protected function setUp()
    {
        $this->_applicationMock = $this->getMock('Magento\AppInterface');
        $this->_sidResolverMock = $this->getMock('\Magento\Session\SidResolverInterface', array(), array(), '', false);
        $this->_model = new ApplicationInitializer(
            $this->_applicationMock,
            $this->_sidResolverMock
        );
    }

    public function testBeforeExecutePerformsRequiredChecks()
    {
        $this->_applicationMock->expects($this->once())->method('requireInstalledInstance');
        $this->_sidResolverMock->expects($this->once())->method('setUseSessionInUrl')->with(false);
        $this->_model->beforeExecute(array());
    }
}
