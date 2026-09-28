<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Console\Command;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Customer\Model\AuthenticationInterface;

/**
 * Test password hash upgrade command.
 *
 * @magentoAppArea frontend
 */
class UpgradeHashAlgorithmCommandTest extends TestCase
{
    /**
     * @var UpgradeHashAlgorithmCommand
     */
    private $command;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var CustomerRegistry
     */
    private $registry;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->command = Bootstrap::getObjectManager()->get(UpgradeHashAlgorithmCommand::class);
        $this->customerFactory = Bootstrap::getObjectManager()->get(CustomerFactory::class);
        $this->encryptor = Bootstrap::getObjectManager()->get(EncryptorInterface::class);
        $this->registry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $this->authentication = Bootstrap::getObjectManager()->get(AuthenticationInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * Test upgrading a customer's password hash.
     *
     * @return void
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpgrades(): void
    {
        $latestVersion = $this->encryptor->getLatestHashVersion();
        $original = 'password';
        //Hash the customer's password with the oldest algorithm.
        /** @var Customer $customer */
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($this->storeManager->getWebsite()->getId());
        $customer->loadByEmail('customer@example.com');
        $customer->setPasswordHash($this->encryptor->getHash($original, true, Encryptor::HASH_VERSION_MD5));
        $customer->save();
        $this->registry->remove($customer->getId());

        //Upgrading customers' password hashes 1 more times than there are algorithms to be sure
        //that running the command won't break anything
        $tester = new CommandTester($this->command);
        for ($cycle = 0; $cycle < $latestVersion; $cycle++) {
            $tester->execute([]);
        }

        /** @var Customer $updated */
        $updated = $this->customerFactory->create();
        $updated->setWebsiteId($this->storeManager->getWebsite()->getId());
        $updated->loadByEmail('customer@example.com');

        //Using the latest algorithm
        $this->assertMatchesRegularExpression(
            '/\:' .$this->encryptor->getLatestHashVersion() .'[^\:]*?$/',
            $updated->getPasswordHash()
        );

        //Able to log in
        $this->assertTrue($this->authentication->authenticate($updated->getId(), $original));
    }
}
