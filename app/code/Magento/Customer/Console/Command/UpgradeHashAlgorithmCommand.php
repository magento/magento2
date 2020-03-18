<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Console\Command;

use Magento\Framework\Encryption\Encryptor;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Upgrade users passwords to the new algorithm
 */
class UpgradeHashAlgorithmCommand extends Command
{
    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var CustomerResourceModel
     */
    private $resourceModel;

    /**
     * UpgradeHashAlgorithmCommand constructor.
     *
     * @param Encryptor $encryptor
     * @param CustomerResourceModel $resourceModel
     */
    public function __construct(
        Encryptor $encryptor,
        CustomerResourceModel $resourceModel
    ) {
        parent::__construct();

        $this->encryptor = $encryptor;
        $this->resourceModel = $resourceModel;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('customer:hash:upgrade')
            ->setDescription('Upgrade customer\'s hash according to the latest algorithm');
    }

    /**
     * Executes 'customer:hash:upgrade' command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $customers = $this->getCustomers();
        foreach ($customers as $customer) {
            if (!$this->encryptor->validateHashVersion($customer['password_hash'])) {
                $customerId = (int)$customer['entity_id'];
                $currentPasswordHash = $customer['password_hash'];
                $newPasswordHash = $this->generateNewPasswordHash($currentPasswordHash);
                $this->updateCustomerPasswordHash($customerId, $newPasswordHash);

                $output->write(".");
            }
        }

        $output->writeln(".");
        $output->writeln("<info>Finished</info>");
    }

    /**
     * Returns array of customers
     *
     * @return array
     */
    private function getCustomers(): array
    {
        $connection = $this->resourceModel->getConnection();
        $tableName = $this->resourceModel->getTable('customer_entity');
        $select = $connection->select();
        $select->from(
            $tableName,
            [
                'entity_id', 'password_hash'
            ]
        );

        return $connection->fetchAll($select);
    }

    /**
     * Creates a new hash
     *
     * @param string $passwordHash
     * @return string
     */
    private function generateNewPasswordHash(string $passwordHash = ''): string
    {
        list($hash, $salt, $version) = explode(Encryptor::DELIMITER, $passwordHash, 3);
        $version .= Encryptor::DELIMITER . $this->encryptor->getLatestHashVersion();
        $hash = $this->encryptor->getHash($hash, $salt, $this->encryptor->getLatestHashVersion());
        list($hash, $salt) = explode(Encryptor::DELIMITER, $hash, 3);

        return implode(Encryptor::DELIMITER, [$hash, $salt, $version]);
    }

    /**
     * Updates `customer_entity`.`password_hash`
     *
     * @param int $customerId
     * @param string $passwordHash
     */
    private function updateCustomerPasswordHash(int $customerId = 0, string $passwordHash = ''): void
    {
        $connection = $this->resourceModel->getConnection();
        $connection->update(
            $this->resourceModel->getTable('customer_entity'),
            [
                'password_hash' => $passwordHash
            ],
            $connection->quoteInto('entity_id = ?', $customerId)
        );
    }
}
