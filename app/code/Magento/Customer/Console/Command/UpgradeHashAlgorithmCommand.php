<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Console\Command;

use Magento\Customer\Model\Customer;
use Magento\Framework\Encryption\Encryptor;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Upgrade users passwords to the new algorithm
 */
class UpgradeHashAlgorithmCommand extends Command
{
    /**
     * @var CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @param CollectionFactory $customerCollectionFactory
     * @param Encryptor $encryptor
     */
    public function __construct(
        CollectionFactory $customerCollectionFactory,
        Encryptor $encryptor
    ) {
        parent::__construct();
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->encryptor = $encryptor;
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
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->collection = $this->customerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $customerCollection = $this->collection->getItems();
        /** @var $customer Customer */
        foreach ($customerCollection as $customer) {
            $customer->load($customer->getId());
            if (!$this->encryptor->validateHashVersion($customer->getPasswordHash())) {
                list($hash, $salt, $version) = explode(Encryptor::DELIMITER, $customer->getPasswordHash(), 3);
                $hash = $this->encryptor->getHash($hash, $salt, $this->encryptor->getLatestHashVersion());
                list($hash, $salt, $newVersion) = explode(Encryptor::DELIMITER, $hash, 3);
                $hash = implode(Encryptor::DELIMITER, [$hash, $salt, $version .Encryptor::DELIMITER .$newVersion]);
                $customer->setPasswordHash($hash);
                $customer->save();
                $output->write(".");
            }
        }
        $output->writeln(".");
        $output->writeln("<info>Finished</info>");

        return 0;
    }
}
