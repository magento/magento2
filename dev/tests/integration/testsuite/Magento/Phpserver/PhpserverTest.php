<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Phpserver;

/**
 * @magentoAppIsolation enabled
 *
 * @magentoConfigFixture current_store web/secure/base_url http://127.0.0.1:8082/
 * @magentoConfigFixture current_store web/unsecure/base_link_url http://127.0.0.1:8082/
 * @magentoConfigFixture current_store web/secure/base_link_url http://127.0.0.1:8082/
 * @magentoConfigFixture current_store web/secure/use_in_frontend 0
 *
 * @magentoAppArea frontend
 */
class PhpserverTest extends \PHPUnit\Framework\TestCase
{
    const BASE_URL = '127.0.0.1:8082';

    private static $serverPid;

    /**
     * @var \Zend\Http\Client
     */
    private $httpClient;

    /**
     * Instantiate phpserver in the pub folder
     */
    public static function setUpBeforeClass()
    {
        if (!(defined('TRAVIS') && TRAVIS === true)) {
            self::markTestSkipped('Travis environment test');
        }
        $return = [];

        $baseDir = __DIR__ . '/../../../../../../';
        $command = sprintf(
            'cd %s && php -S %s -t ./pub/ ./phpserver/router.php >/dev/null 2>&1 & echo $!',
            $baseDir,
            static::BASE_URL
        );
        exec($command, $return);
        static::$serverPid = (int) $return[0];
    }

    private function getUrl($url)
    {
        return sprintf('http://%s/%s', self::BASE_URL, ltrim($url, '/'));
    }

    public function setUp()
    {
        $this->httpClient = new \Zend\Http\Client(null, ['timeout' => 10]);
    }

    public function testServerHasPid()
    {
        $this->assertTrue(static::$serverPid > 0);
    }

    public function testServerResponds()
    {
        $this->httpClient->setUri($this->getUrl('/'));
        $response = $this->httpClient->send();
        $this->assertFalse($response->isClientError());
    }

    public function testStaticCssFile()
    {
        $this->httpClient->setUri($this->getUrl('/errors/default/css/styles.css'));
        $response = $this->httpClient->send();

        $this->assertFalse($response->isClientError());
        $this->assertStringStartsWith('text/css', $response->getHeaders()->get('Content-Type')->getMediaType());
    }

    public function testStaticImageFile()
    {
        $this->httpClient->setUri($this->getUrl('/errors/default/images/logo.gif'));
        $response = $this->httpClient->send();

        $this->assertFalse($response->isClientError());
        $this->assertStringStartsWith('image/gif', $response->getHeaders()->get('Content-Type')->getMediaType());
    }

    public static function tearDownAfterClass()
    {
        posix_kill(static::$serverPid, SIGKILL);
    }
}
