<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\Test\Unit\Setup;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Setup\InstallConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InstallConfigTest extends TestCase
{
    /**
     * @var InstallConfig
     */
    private $installConfig;

    /**
     * @var WriterInterface|MockObject
     */
    private $configWriterMock;

    /**
     * @inheritdoc
     */
    protected function setup(): void
    {
        $this->configWriterMock = $this->getMockBuilder(WriterInterface::class)->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->installConfig = $objectManager->getObject(
            InstallConfig::class,
            [
                'configWriter' => $this->configWriterMock,
                'searchConfigMapping' => [
                    'elasticsearch-host' => 'elasticsearch7_server_hostname',
                    'elasticsearch-port' => 'elasticsearch7_server_port',
                    'elasticsearch-timeout' => 'elasticsearch7_server_timeout',
                    'elasticsearch-index-prefix' => 'elasticsearch7_index_prefix',
                    'elasticsearch-enable-auth' => 'elasticsearch7_enable_auth',
                    'elasticsearch-username' => 'elasticsearch7_username',
                    'elasticsearch-password' => 'elasticsearch7_password'
                ]
            ]
        );
    }

    /**
     * @return void
     */
    public function testConfigure(): void
    {
        $inputOptions = [
            'search-engine' => 'elasticsearch7',
            'elasticsearch-host' => 'localhost',
            'elasticsearch-port' => '9200'
        ];

        $this->configWriterMock
            ->method('save')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == 'catalog/search/engine' && $arg2 == 'elasticsearch7') {
                    return null;
                } elseif ($arg1 == 'catalog/search/elasticsearch7_server_hostname' && $arg2 == 'localhost') {
                    return null;
                } elseif ($arg1 == 'catalog/search/elasticsearch7_server_port' && $arg2 == '9200') {
                    return null;
                }
            });

        $this->installConfig->configure($inputOptions);
    }

    /**
     * @return void
     */
    public function testConfigureWithEmptyInput(): void
    {
        $this->configWriterMock->expects($this->never())->method('save');
        $this->installConfig->configure([]);
    }
}
