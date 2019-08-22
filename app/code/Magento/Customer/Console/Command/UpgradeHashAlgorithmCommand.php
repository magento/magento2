<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Console\Command;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
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
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * UpgradeHashAlgorithmCommand constructor.
     *
     * @param CollectionFactory $customerCollectionFactory
     * @param Encryptor $encryptor
     * @param CustomerRepositoryInterface|null $customerRepository
     */
    public function __construct(
        CollectionFactory $customerCollectionFactory,
        Encryptor $encryptor,
        CustomerRepositoryInterface $customerRepository = null
    ) {
        parent::__construct();
        $this->encryptor = $encryptor;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerRepository = $customerRepository ?: ObjectManager::getInstance()
            ->get(CustomerRepositoryInterface::class);
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
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->collection = $this->customerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $customerCollection = $this->collection->getItems();
        /** @var $customer Customer */
        foreach ($customerCollection as $customer) {
            if (!$this->encryptor->validateHashVersion($customer->getPasswordHash())) {
                list($hash, $salt, $version) = explode(Encryptor::DELIMITER, $customer->getPasswordHash(), 3);
                $version .= Encryptor::DELIMITER . $this->encryptor->getLatestHashVersion();
                $hash = $this->encryptor->getHash($hash, $salt, $this->encryptor->getLatestHashVersion());
                list($hash, $salt) = explode(Encryptor::DELIMITER, $hash, 3);
                $hash = implode(Encryptor::DELIMITER, [$hash, $salt, $version]);
                $customerDataObject = $this->getCustomerDataObject($customer->getId());
                $this->customerRepository->save($customerDataObject, $hash);
                $output->write(".");
            }
        }
        $output->writeln(".");
        $output->writeln("<info>Finished</info>");
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomerDataObject(int $customerId)
    {
        return $this->customerRepository->getById($customerId);
    }
}
