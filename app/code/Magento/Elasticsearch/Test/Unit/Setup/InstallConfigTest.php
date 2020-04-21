<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Setup;

use Magento\Elasticsearch\Setup\ConnectionValidator;
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
     * @var ConnectionValidator|MockObject
     */
    private $validatorMock;

    /**
     * @var WriterInterface|MockObject
     */
    private $configWriterMock;

    protected function setup()
    {
        $this->validatorMock = $this->getMockBuilder(ConnectionValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configWriterMock = $this->getMockBuilder(WriterInterface::class)->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->installConfig = $objectManager->getObject(
            InstallConfig::class,
            [
                'configWriter' => $this->configWriterMock,
                'validator' => $this->validatorMock,
                'searchConfigMapping' => [
                    'elasticsearch-host' => 'elasticsearch5_server_hostname',
                    'elasticsearch-port' => 'elasticsearch5_server_port',
                    'elasticsearch-timeout' => 'elasticsearch5_server_timeout',
                    'elasticsearch-index-prefix' => 'elasticsearch5_index_prefix',
                    'elasticsearch-enable-auth' => 'elasticsearch5_enable_auth',
                    'elasticsearch-username' => 'elasticsearch5_username',
                    'elasticsearch-password' => 'elasticsearch5_password',
                ]
            ]
        );
    }

    public function testConfigure()
    {
        $inputOptions = [
            'search-engine' => 'elasticsearch5',
            'elasticsearch-host' => 'localhost',
            'elasticsearch-port' => '9200'
        ];

        $this->configWriterMock
            ->expects($this->at(0))
            ->method('save')
            ->with('catalog/search/engine', 'elasticsearch5');
        $this->configWriterMock
            ->expects($this->at(1))
            ->method('save')
            ->with('catalog/search/elasticsearch5_server_hostname', 'localhost');
        $this->configWriterMock
            ->expects($this->at(2))
            ->method('save')
            ->with('catalog/search/elasticsearch5_server_port', '9200');

        $this->validatorMock->expects($this->once())->method('validate')->with('elasticsearch5')->willReturn(true);

        $this->installConfig->configure($inputOptions);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Connection to Elasticsearch cannot be established.
     */
    public function testConfigureValidateFail()
    {
        $inputOptions = [
            'search-engine' => 'elasticsearch5',
            'elasticsearch-host' => 'es.domain.com',
            'elasticsearch-port' => '9200'
        ];

        $this->configWriterMock
            ->expects($this->at(0))
            ->method('save')
            ->with('catalog/search/engine', 'elasticsearch5');
        $this->configWriterMock
            ->expects($this->at(1))
            ->method('save')
            ->with('catalog/search/elasticsearch5_server_hostname', 'es.domain.com');
        $this->configWriterMock
            ->expects($this->at(2))
            ->method('save')
            ->with('catalog/search/elasticsearch5_server_port', '9200');

        $this->validatorMock->expects($this->once())->method('validate')->with('elasticsearch5')->willReturn(false);

        $this->installConfig->configure($inputOptions);
    }

    public function testConfigureWithEmptyInput()
    {
        $this->configWriterMock->expects($this->never())->method('save');
        $this->validatorMock->expects($this->once())->method('validate')->with(null)->willReturn(true);
        $this->installConfig->configure([]);
    }
}
