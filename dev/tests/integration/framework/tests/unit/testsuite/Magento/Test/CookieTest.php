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

class CookieTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Name of the sample cookie to be used in tests
     */
    const SAMPLE_COOKIE_NAME = 'sample_cookie';

    /**
     * @var \Magento\TestFramework\Cookie
     */
    protected $_model;

    protected function setUp()
    {
        $coreStoreConfig = $this->getMockBuilder('Magento\Core\Model\Store\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_model = new \Magento\TestFramework\Cookie(
            $coreStoreConfig,
            $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false),
            new \Magento\TestFramework\Request(
                $this->getMock('Magento\App\RouterListInterface'),
                'http://example.com',
                $this->getMock('Magento\App\Request\PathInfoProcessorInterface')
            ),
            new \Magento\TestFramework\Response(
                $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false)
            )
        );
    }

    public function testSet()
    {
        $cookieValue = 'some_cookie_value';
        $this->assertFalse($this->_model->get(self::SAMPLE_COOKIE_NAME));
        $this->_model->set(self::SAMPLE_COOKIE_NAME, $cookieValue);
        $this->assertEquals($cookieValue, $this->_model->get(self::SAMPLE_COOKIE_NAME));
        $this->assertEquals($cookieValue, $_COOKIE[self::SAMPLE_COOKIE_NAME]);
    }

    public function testDelete()
    {
        $this->_model->set(self::SAMPLE_COOKIE_NAME, 'some_value');
        $this->_model->delete(self::SAMPLE_COOKIE_NAME);
        $this->assertFalse($this->_model->get(self::SAMPLE_COOKIE_NAME));
        $this->assertArrayNotHasKey(self::SAMPLE_COOKIE_NAME, $_COOKIE);
    }
}
