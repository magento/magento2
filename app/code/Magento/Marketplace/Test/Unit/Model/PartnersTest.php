<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Marketplace\Model\Partners;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Marketplace\Helper\Cache;

class PartnersTest extends TestCase
{
    /**
     * @var MockObject|Partners
     */
    private $partnersModelMock;

    private $returnPackages = '
                 {
                    "partners": {
                        "1": {
                            "url_page": "http://test.com",
                            "url_partner_page": "http://test.com",
                            "img": "http://test.com/img",
                            "title": "Test page",
                            "description": "Test page description"
                        },
                        "2": {
                            "url_page": "http://test.com",
                            "url_partner_page": "http://test.com",
                            "img": "http://test.com/img",
                            "title": "Test page",
                            "description": "Test page description"
                        }
                    }
                 }';

    protected function setUp(): void
    {
        $this->partnersModelMock = $this->getPartnersModelMock(
            [
                'getApiUrl',
                'getCurlClient',
                'getCache',
                'getReferer'
            ]
        );
    }

    /**
     * @var string
     */
    protected $apiUrl = 'www.testpackages';

    /**
     * @covers \Magento\Marketplace\Model\Partners::getPartners
     */
    public function testGetPartners()
    {
        $this->partnersModelMock->expects($this->once())
            ->method('getApiUrl')
            ->will($this->returnValue($this->apiUrl));

        $curlMock = $this->getCurlMock(['post', 'getBody', 'setOptions']);
        $curlMock->expects($this->once())
            ->method('post');
        $curlMock->expects($this->once())
            ->method('setOptions');
        $curlMock->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($this->returnPackages));
        $this->partnersModelMock->expects($this->exactly(3))
            ->method('getCurlClient')
            ->will($this->returnValue($curlMock));

        $cacheMock = $this->getCacheMock(['savePartnersToCache']);
        $cacheMock->expects($this->once())
            ->method('savePartnersToCache');
        $this->partnersModelMock->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cacheMock));
        $this->partnersModelMock->expects($this->once())
            ->method('getReferer');

        $this->partnersModelMock->getPartners();
    }

    /**
     * @covers \Magento\Marketplace\Model\Partners::getPartners
     */
    public function testGetPartnersException()
    {
        $this->partnersModelMock->expects($this->once())
            ->method('getApiUrl')
            ->will($this->returnValue($this->apiUrl));

        $curlMock = $this->getCurlMock(['post', 'getBody', 'setOptions']);
        $curlMock->expects($this->once())
            ->method('post');
        $curlMock->expects($this->once())
            ->method('getBody')
            ->will($this->throwException(new \Exception));
        $this->partnersModelMock->expects($this->exactly(3))
            ->method('getCurlClient')
            ->will($this->returnValue($curlMock));

        $cacheMock = $this->getCacheMock(['savePartnersToCache', 'loadPartnersFromCache']);
        $cacheMock->expects($this->never())
            ->method('savePartnersToCache');
        $cacheMock->expects($this->once())
            ->method('loadPartnersFromCache');
        $this->partnersModelMock->expects($this->once())
            ->method('getCache')
            ->will($this->returnValue($cacheMock));
        $this->partnersModelMock->expects($this->once())
            ->method('getReferer');

        $this->partnersModelMock->getPartners();
    }

    /**
     * Gets partners block mock
     *
     * @return MockObject|\Magento\Marketplace\Block\Partners
     */
    public function getPartnersBlockMock($methods = null)
    {
        return $this->createPartialMock(\Magento\Marketplace\Block\Partners::class, $methods);
    }

    /**
     * Gets partners model mock
     *
     * @return MockObject|Partners
     */
    public function getPartnersModelMock($methods)
    {
        return $this->createPartialMock(Partners::class, $methods, []);
    }

    /**
     * Gets partners model mock
     *
     * @return MockObject|Curl
     */
    public function getCurlMock($methods)
    {
        return $this->createPartialMock(Curl::class, $methods, []);
    }

    /**
     * Gets cache mock
     *
     * @return MockObject|Curl
     */
    public function getCacheMock($methods)
    {
        return $this->createPartialMock(Cache::class, $methods, []);
    }
}
