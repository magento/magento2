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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\TestFramework\Application::getInstallDir()
     * @covers \Magento\TestFramework\Application::getDbInstance()
     * @covers \Magento\TestFramework\Application::getInitParams()
     */
    public function testConstructor()
    {
        $dbInstance = $this->getMockForAbstractClass('Magento\TestFramework\Db\AbstractDb', array(), '', false);
        $installDir = '/install/dir';
        $appMode = \Magento\App\State::MODE_DEVELOPER;

        $object = new \Magento\TestFramework\Application(
            $dbInstance,
            $installDir,
            new \Magento\Simplexml\Element('<data/>'),
            '',
            array(),
            $appMode
        );

        $this->assertSame($dbInstance, $object->getDbInstance(), 'Db instance is not set in Application');
        $this->assertEquals($installDir, $object->getInstallDir(), 'Install directory is not set in Application');

        $initParams = $object->getInitParams();
        $this->assertInternalType('array', $initParams, 'Wrong initialization parameters type');
        $this->assertArrayHasKey(\Magento\Core\Model\App::PARAM_APP_DIRS, $initParams,
            'Directories are not configured');
        $this->assertArrayHasKey(\Magento\Core\Model\App::PARAM_MODE, $initParams,
            'Application mode is not configured');
        $this->assertEquals(
            \Magento\App\State::MODE_DEVELOPER,
            $initParams[\Magento\Core\Model\App::PARAM_MODE],
            'Wrong application mode configured'
        );
    }
}
