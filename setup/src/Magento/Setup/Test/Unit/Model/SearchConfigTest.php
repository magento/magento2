<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\SearchEngine\Validator;
use Magento\Search\Setup\CompositeInstallConfig;
use Magento\Setup\Model\SearchConfig;
use Magento\Setup\Model\SearchConfigOptionsList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SearchConfigTest extends TestCase
{
    /**
     * @var SearchConfig
     */
    private $searchConfig;

    /**
     * @var SearchConfigOptionsList
     */
    private $searchConfigOptionsList;

    /**
     * @var CompositeInstallConfig|MockObject
     */
    private $installConfigMock;

    /**
     * @var Validator|MockObject
     */
    private $searchEngineValidatorMock;

    protected function setUp(): void
    {
        $this->installConfigMock = $this->getMockBuilder(CompositeInstallConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchEngineValidatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->searchConfigOptionsList = $objectManager->getObject(SearchConfigOptionsList::class);
        $this->searchConfig = $objectManager->getObject(
            SearchConfig::class,
            [
                'searchConfigOptionsList' => $this->searchConfigOptionsList,
                'searchValidator' => $this->searchEngineValidatorMock,
                'installConfig' => $this->installConfigMock
            ]
        );
    }

    /**
     * @param array $installInput
     * @param array $searchInput
     * @dataProvider installInputDataProvider
     */
    public function testSaveConfiguration(array $installInput, array $searchInput)
    {
        $this->installConfigMock->expects($this->once())->method('configure')->with($searchInput);
        $this->searchEngineValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $this->searchConfig->saveConfiguration($installInput);
    }

    /**
     * @param array $installInput
     * @param array $searchInput
     * @dataProvider installInputDataProvider
     */
    public function testSaveConfigurationInvalidSearchEngine(array $installInput, array $searchInput)
    {
        $this->expectException(\Magento\Setup\Exception::class);
        $this->expectExceptionMessage('Search engine \'other-engine\' is not an available search engine.');

        $installInput['search-engine'] = 'other-engine';
        $searchInput['search-engine'] = 'other-engine';
        $this->installConfigMock->expects($this->never())->method('configure');

        $this->searchConfig->saveConfiguration($installInput);
    }

    /**
     * @param array $installInput
     * @param array $searchInput
     * @dataProvider installInputDataProvider
     */
    public function testSaveConfigurationValidationFail(array $installInput, array $searchInput)
    {
        $this->expectException(\Magento\Framework\Validation\ValidationException::class);
        $this->expectExceptionMessage('Could not connect to host');

        $this->installConfigMock->expects($this->once())->method('configure')->with($searchInput);
        $this->searchEngineValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(['Could not connect to host']);

        $this->searchConfig->saveConfiguration($installInput);
    }

    public static function installInputDataProvider()
    {
        return [
            [
                'installInput' => [
                    'amqp-host' => '',
                    'amqp-port' => '5672',
                    'amqp-user' => '',
                    'amqp-password' => '',
                    'amqp-virtualhost' => '/',
                    'amqp-ssl' => '',
                    'amqp-ssl-options' => '',
                    'db-host' => 'localhost',
                    'db-name' => 'magento',
                    'db-user' => 'root',
                    'db-engine' => null,
                    'db-password' => 'root',
                    'db-prefix' => null,
                    'db-model' => null,
                    'db-init-statements' => null,
                    'skip-db-validation' => false,
                    'http-cache-hosts' => null,
                    'base-url' => 'http://magento.dev',
                    'language' => 'en_US',
                    'timezone' => 'America/Chicago',
                    'currency' => 'USD',
                    'use-rewrites' => '1',
                    'use-secure' => null,
                    'base-url-secure' => null,
                    'use-secure-admin' => null,
                    'admin-use-security-key' => null,
                    'search-engine' => 'elasticsearch8',
                    'elasticsearch-host' => 'localhost',
                    'elasticsearch-port' => '9200',
                    'elasticsearch-enable-auth' => false,
                    'elasticsearch-index-prefix' => 'magento2',
                    'elasticsearch-timeout' => 15,
                    'no-interaction' => false,
                ],
                'searchInput' => [
                    'search-engine' => 'elasticsearch8',
                    'elasticsearch-host' => 'localhost',
                    'elasticsearch-port' => '9200',
                    'elasticsearch-enable-auth' => false,
                    'elasticsearch-index-prefix' => 'magento2',
                    'elasticsearch-timeout' => 15,
                ]
            ]
        ];
    }
}
