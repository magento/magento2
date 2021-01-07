<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Phpserver;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

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

    /**
     * @var Process
     */
    private $serverProcess;

    /**
     * @var \Laminas\Http\Client
     */
    private $httpClient;

    private function getUrl($url)
    {
        return sprintf('http://%s/%s', self::BASE_URL, ltrim($url, '/'));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setUp(): void
    {
        $this->httpClient = new \Laminas\Http\Client(null, ['timeout' => 10]);

        /** @var Process $process */
        $phpBinaryFinder = new PhpExecutableFinder();
        $phpBinaryPath = $phpBinaryFinder->find();
        $command = sprintf(
            "%s -S %s -t ./pub ./phpserver/router.php",
            $phpBinaryPath,
            self::BASE_URL
        );
        $this->serverProcess = Process::fromShellCommandline(
            $command,
            realpath(__DIR__ . '/../../../../../../')
        );
        $this->serverProcess->start();
        $this->serverProcess->waitUntil(function ($type, $output) {
            return strpos($output, "Development Server") !== false;
        });
    }

    protected function tearDown(): void
    {
        $this->serverProcess->stop();
    }

    public function testServerHasPid()
    {
        $this->assertTrue($this->serverProcess->getPid() > 0);
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
}
