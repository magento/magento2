<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\GetProfile;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

class GetProfileTest extends TestCase
{
    /**
     * @var ConfigInterface|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;
    /**
     * @var CurlFactory|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private $curlFactoryMock;
    /**
     * @var Json|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private $jsonMock;
    /**
     * @var GetProfile
     */
    private GetProfile $profile;

    /**
     * Prepare test objects.
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->curlFactoryMock = $this->createMock(CurlFactory::class);
        $this->jsonMock = $this->createMock(Json::class);
        $this->profile = new GetProfile(
            $this->configMock,
            $this->curlFactoryMock,
            $this->jsonMock
        );
    }

    /**
     * Test validate token
     */
    public function testGetProfile()
    {
        $curl = $this->createMock(Curl::class);
        $this->curlFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($curl);
        $curl->expects($this->exactly(3))
            ->method('addHeader')
            ->willReturn(null);
        $this->configMock->expects($this->once())
            ->method('getProfileUrl')
            ->willReturn('http://www.some.url.com');
        $curl->expects($this->exactly(2))
            ->method('getBody')
            ->willReturn(null);
        $data = ['email' => 'test@email.com', 'name' => 'Name'];
        $this->jsonMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($data);
        $this->assertEquals($data, $this->profile->getProfile('ftXdatRdsafga'));
    }
}
