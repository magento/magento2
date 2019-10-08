<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Model\Url;

use Magento\Downloadable\Model\DomainManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\DeploymentConfig;

/**
 * Test for Magento\Downloadable\Model\Url\DomainValidator.
 */
class DomainValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DomainValidator
     */
    private $model;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->deploymentConfig = $this->createPartialMock(
            DeploymentConfig::class,
            ['get']
        );

        $domainManager = $objectManager->create(
            DomainManager::class,
            ['deploymentConfig' => $this->deploymentConfig]
        );

        $this->model = $objectManager->create(
            DomainValidator::class,
            ['domainManager' => $domainManager]
        );
    }

    /**
     * @param string $urlInput
     * @param array $envDomainWhitelist
     * @param bool $isValid
     *
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture current_store web/unsecure/base_url http://example.com/
     * @magentoConfigFixture current_store web/secure/base_url https://secure.example.com/
     * @magentoConfigFixture fixture_second_store_store web/unsecure/base_url http://example2.com/
     * @magentoConfigFixture fixture_second_store_store web/secure/base_url https://secure.example2.com/
     * @dataProvider isValidDataProvider
     */
    public function testIsValid(string $urlInput, array $envDomainWhitelist, bool $isValid)
    {
        $this->deploymentConfig
            ->method('get')
            ->with(DomainValidator::PARAM_DOWNLOADABLE_DOMAINS)
            ->willReturn($envDomainWhitelist);

        $this->assertEquals(
            $isValid,
            $this->model->isValid($urlInput),
            'Failed asserting is ' . ($isValid ? 'valid' : 'not valid') . ': ' . $urlInput .
            PHP_EOL .
            'Domain whitelist: ' . implode(', ', $envDomainWhitelist)
        );
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            ['http://example.com', ['example.co'], false],
            [' http://example.com ', ['example.com'], false],
            ['http://example.com', ['example.com'], true],
            ['https://example.com', ['example.com'], true],
            ['https://example.com/downloadable.pdf', ['example.com'], true],
            ['https://example.com:8080/downloadable.pdf', ['example.com'], true],
            ['http://secure.example.com', ['secure.example.com'], true],
            ['https://secure.example.com', ['secure.example.com'], true],
            ['https://ultra.secure.example.com', ['secure.example.com'], false],
            ['http://example2.com', ['example2.com'], true],
            ['https://example2.com', ['example2.com'], true],
            ['http://subdomain.example2.com', ['example2.com'], false],
            ['https://adobe.com', ['adobe.com'], true],
            ['https://subdomain.adobe.com', ['adobe.com'], false],
            ['https://ADOBE.COm', ['adobe.com'], true],
            ['https://adobe.com', ['ADOBE.COm'], true],
            ['http://127.0.0.1', ['127.0.0.1'], false],
            ['http://[::1]', ['::1'], false],
        ];
    }
}
