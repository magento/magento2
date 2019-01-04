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
    const METHOD_CODE = 'authorizenet_acceptjs';

    /**
     * @var Config
     */
    private $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Config::class, [
            'scopeConfig' => $this->scopeConfigMock,
            'methodCode' => self::METHOD_CODE,
        ]);
    }

    public function testGetApiUrl()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_API_URL), ScopeInterface::SCOPE_STORE, null)
            ->willReturn('abc');
        $this->assertEquals('abc', $this->model->getApiUrl());
    }

    public function testGetTransactionKey()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_TRANSACTION_KEY), ScopeInterface::SCOPE_STORE, null)
            ->willReturn('abc');
        $this->assertEquals('abc', $this->model->getTransactionKey());
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
