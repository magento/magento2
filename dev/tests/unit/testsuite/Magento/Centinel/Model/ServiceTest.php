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

/**
 * Test class for \Magento\Centinel\Model\Service
 */
namespace Magento\Centinel\Model;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Centinel\Model\Service::getAuthenticationStartUrl
     * @covers \Magento\Centinel\Model\Service::_getUrl
     */
    public function testGetAuthenticationStartUrl()
    {
        $url = $this->getMock('Magento\Core\Model\Url', array('getUrl'), array(), '', false);
        $url->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo('url_prefix/authenticationstart'))
            ->will($this->returnValue('some value'));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var Service $model */
        $model = $helper->getObject(
            'Magento\Centinel\Model\Service',
            array('url' => $url, 'urlPrefix' => 'url_prefix/')
        );
        $this->assertEquals('some value', $model->getAuthenticationStartUrl());
    }
}
