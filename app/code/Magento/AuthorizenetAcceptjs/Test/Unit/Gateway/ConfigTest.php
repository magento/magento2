<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    private const METHOD_CODE = 'authorizenet_acceptjs';

    /**
     * @var Config
     */
    private $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'methodCode' => self::METHOD_CODE,
            ]
        );
    }

    public function testGetApiUrlProduction()
    {
        $this->scopeConfigMock->method('getValue')
            ->with($this->getPath('environment'), ScopeInterface::SCOPE_STORE, 123)
            ->willReturn('production');
        $this->assertEquals('https://api.authorize.net/xml/v1/request.api', $this->model->getApiUrl(123));
    }

    public function testGetApiUrlSandbox()
    {
        $this->scopeConfigMock->method('getValue')
            ->with($this->getPath('environment'), ScopeInterface::SCOPE_STORE, 123)
            ->willReturn('sandbox');
        $this->assertEquals('https://apitest.authorize.net/xml/v1/request.api', $this->model->getApiUrl(123));
    }

    public function testGetTransactionKey()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath('trans_key'), ScopeInterface::SCOPE_STORE, 123)
            ->willReturn('abc');
        $this->assertEquals('abc', $this->model->getTransactionKey(123));
    }

    public function testGetTransactionHash()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath('trans_md5'), ScopeInterface::SCOPE_STORE, 123)
            ->willReturn('myhash');
        $this->assertEquals('myhash', $this->model->getLegacyTransactionHash(123));
    }

    public function testGetSolutionIdSandbox()
    {
        $this->scopeConfigMock->method('getValue')
            ->with($this->getPath('environment'), ScopeInterface::SCOPE_STORE, 123)
            ->willReturn('sandbox');
        $this->assertEquals('AAA102993', $this->model->getSolutionId(123));
    }
    public function testGetSolutionIdProduction()
    {
        $this->scopeConfigMock->method('getValue')
            ->with($this->getPath('environment'), ScopeInterface::SCOPE_STORE, 123)
            ->willReturn('production');
        $this->assertEquals('', $this->model->getSolutionId(123));
    }

    /**
     * Return config path
     *
     * @param string $field
     * @return string
     */
    private function getPath($field)
    {
        return sprintf(Config::DEFAULT_PATH_PATTERN, self::METHOD_CODE, $field);
    }
}
