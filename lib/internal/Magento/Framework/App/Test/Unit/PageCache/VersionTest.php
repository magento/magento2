<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\PageCache;

use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class VersionTest extends TestCase
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
     * @var CookieManagerInterface|MockObject
     */
    protected $cookieManagerMock;

    /**
     * Cookie manager mock
     *
     * @var CookieMetadataFactory|MockObject
     */
    protected $cookieMetadataFactoryMock;

    /**
     * Request mock
     *
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * Create cookie and request mock, version instance
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->cookieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(
            CookieMetadataFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->version = $objectManager->getObject(
            Version::class,
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
        $this->requestMock->expects($this->once())->method('isPost')->willReturn($isPost);
        if ($isPost) {
            $publicCookieMetadataMock = $this->createMock(PublicCookieMetadata::class);
            $publicCookieMetadataMock->expects($this->once())
                ->method('setPath')
                ->with('/')->willReturnSelf();

            $publicCookieMetadataMock->expects($this->once())
                ->method('setDuration')
                ->with(Version::COOKIE_PERIOD)->willReturnSelf();

            $publicCookieMetadataMock->expects($this->once())
                ->method('setSecure')
                ->with(false)->willReturnSelf();

            $publicCookieMetadataMock->expects($this->once())
                ->method('setHttpOnly')
                ->with(false)->willReturnSelf();

            $publicCookieMetadataMock->expects($this->once())
                ->method('setSameSite')
                ->with('Lax')->willReturnSelf();

            $this->cookieMetadataFactoryMock->expects($this->once())
                ->method('createPublicCookieMetadata')
                ->with()
                ->willReturn(
                    $publicCookieMetadataMock
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
