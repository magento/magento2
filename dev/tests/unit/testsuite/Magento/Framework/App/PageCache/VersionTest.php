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
namespace Magento\Framework\App\PageCache;

use Magento\TestFramework\ObjectManager;

class VersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Version instance
     *
     * @var Version
     */
    protected $version;

    /**
     * Cookie manager mock
     *
     * @var \Magento\Framework\Stdlib\CookieManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManagerMock;

    /**
     * Cookie manager mock
     *
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieMetadataFactoryMock;

    /**
     * Request mock
     *
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * Create cookie and request mock, version instance
     */
    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->cookieManagerMock = $this->getMockBuilder('Magento\Framework\Stdlib\CookieManager')
            ->disableOriginalConstructor()->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Stdlib\Cookie\CookieMetadataFactory'
        )
            ->disableOriginalConstructor()->getMock();
        $this->version = $objectManager->getObject(
            'Magento\Framework\App\PageCache\Version',
            [
                'cookieManager' => $this->cookieManagerMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'request' => $this->requestMock
            ]
        );
    }

    /**
     * Handle private content version cookie
     * Set cookie if it is not set.
     * Increment version on post requests.
     * In all other cases do nothing.
     */
    /**
     * @dataProvider processProvider
     * @param bool $isPost
     */
    public function testProcess($isPost)
    {
        $this->requestMock->expects($this->once())->method('isPost')->will($this->returnValue($isPost));
        if ($isPost) {
            $publicCookieMetadataMock = $this->getMock('Magento\Framework\Stdlib\Cookie\PublicCookieMetadata');
            $publicCookieMetadataMock->expects($this->once())
                ->method('setPath')
                ->with('/')
                ->will($this->returnSelf());

            $publicCookieMetadataMock->expects($this->once())
                ->method('setDuration')
                ->with(Version::COOKIE_PERIOD)
                ->will($this->returnSelf());

            $publicCookieMetadataMock->expects($this->once())
                ->method('setHttpOnly')
                ->with(false)
                ->will($this->returnSelf());

            $this->cookieMetadataFactoryMock->expects($this->once())
                ->method('createPublicCookieMetadata')
                ->with()
                ->will(
                    $this->returnValue($publicCookieMetadataMock)
                );

            $this->cookieManagerMock->expects($this->once())
                ->method('setPublicCookie');
        }
        $this->version->process();
    }

    /**
     * Data provider for testProcess
     *
     * @return array
     */
    public function processProvider()
    {
        return [
            "post" => [true],
            "notPost" => [false]
        ];
    }
}
