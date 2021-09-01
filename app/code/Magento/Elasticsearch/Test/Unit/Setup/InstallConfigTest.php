<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Setup;

use Magento\Elasticsearch\Setup\InstallConfig;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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
                    'elasticsearch-host' => 'elasticsearch5_server_hostname',
                    'elasticsearch-port' => 'elasticsearch5_server_port',
                    'elasticsearch-timeout' => 'elasticsearch5_server_timeout',
                    'elasticsearch-index-prefix' => 'elasticsearch5_index_prefix',
                    'elasticsearch-enable-auth' => 'elasticsearch5_enable_auth',
                    'elasticsearch-username' => 'elasticsearch5_username',
                    'elasticsearch-password' => 'elasticsearch5_password'
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
            'search-engine' => 'elasticsearch5',
            'elasticsearch-host' => 'localhost',
            'elasticsearch-port' => '9200'
        ];

        $this->configWriterMock
            ->method('save')
            ->withConsecutive(
                ['catalog/search/engine', 'elasticsearch5'],
                ['catalog/search/elasticsearch5_server_hostname', 'localhost'],
                ['catalog/search/elasticsearch5_server_port', '9200']
            );

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
