<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\App\Config\Type;

use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SystemEnvironmentEncryptedTest extends TestCase
{
    /**
     * @var System
     */
    private System $system;

    /**
     * @var array
     */
    private array $environment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->environment = $_ENV;
        unset(
            $_ENV['CONFIG__DEFAULT__CARRIERS__DHL__PASSWORD'],
            $_ENV['CONFIG__WEBSITES__BASE__CARRIERS__DHL__PASSWORD'],
            $_ENV['CONFIG__STORES__DEFAULT__CARRIERS__DHL__PASSWORD'],
            $_ENV['CONFIG__DEFAULT__CARRIERS__UPS__PASSWORD'],
            $_ENV['CONFIG__WEBSITES__BASE__CARRIERS__UPS__PASSWORD'],
            $_ENV['CONFIG__STORES__DEFAULT__CARRIERS__UPS__PASSWORD']
        );
        $this->system = Bootstrap::getObjectManager()->create(System::class);
        $this->system->clean();
    }

    protected function tearDown(): void
    {
        $_ENV = $this->environment;
        $this->system->clean();
        parent::tearDown();
    }

    public function testDefaultPlaintextIsInheritedVerbatim(): void
    {
        $_ENV['CONFIG__DEFAULT__CARRIERS__DHL__PASSWORD'] = 'plain secret ';
        $this->system->clean();

        foreach (['default', 'websites/base', 'stores/default'] as $scope) {
            $this->assertSame('plain secret ', $this->system->get($scope . '/carriers/dhl/password'));
        }
    }

    public function testDefaultCiphertextIsDecryptedAtEveryScope(): void
    {
        $_ENV['CONFIG__DEFAULT__CARRIERS__UPS__PASSWORD'] = Bootstrap::getObjectManager()
            ->get(EncryptorInterface::class)
            ->encrypt('pre-encrypted');
        $this->system->clean();

        foreach (['default', 'websites/base', 'stores/default'] as $scope) {
            $this->assertSame('pre-encrypted', $this->system->get($scope . '/carriers/ups/password'));
        }
    }

    public function testWebsitePlaintextIsInheritedOnlyByItsStore(): void
    {
        $defaultValue = $this->system->get('default/carriers/dhl/password');
        $_ENV['CONFIG__WEBSITES__BASE__CARRIERS__DHL__PASSWORD'] = 'website-plain';
        $this->system->clean();

        $this->assertSame($defaultValue, $this->system->get('default/carriers/dhl/password'));
        $this->assertSame('website-plain', $this->system->get('websites/base/carriers/dhl/password'));
        $this->assertSame('website-plain', $this->system->get('stores/default/carriers/dhl/password'));
    }

    public function testStoreCiphertextIsDecrypted(): void
    {
        $_ENV['CONFIG__STORES__DEFAULT__CARRIERS__DHL__PASSWORD'] = Bootstrap::getObjectManager()
            ->get(EncryptorInterface::class)
            ->encrypt('store-secret');
        $this->system->clean();

        $this->assertSame('store-secret', $this->system->get('stores/default/carriers/dhl/password'));
    }

    #[\Magento\TestFramework\Fixture\DbIsolation(true)]
    public function testStoreDatabaseValueWinsOverInheritedEnvironmentValue(): void
    {
        $_ENV['CONFIG__DEFAULT__CARRIERS__DHL__PASSWORD'] = 'env-plain';
        $objectManager = Bootstrap::getObjectManager();
        $storeId = $objectManager->get(StoreManagerInterface::class)->getStore('default')->getId();
        $objectManager->create(Encrypted::class)
            ->setPath('carriers/dhl/password')
            ->setScope('stores')
            ->setScopeId($storeId)
            ->setValue('db-secret')
            ->save();
        $this->system->clean();

        $this->assertSame('db-secret', $this->system->get('stores/default/carriers/dhl/password'));
        $this->assertSame('env-plain', $this->system->get('default/carriers/dhl/password'));
        $this->assertSame('env-plain', $this->system->get('websites/base/carriers/dhl/password'));
    }
}
