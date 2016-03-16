<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSampleData\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Class Order
 */
class Order
{
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var Order\Converter
     */
    protected $converter;

    /**
     * @var Order\Processor
     */
    protected $orderProcessor;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param Order\Converter $converter
     * @param Order\Processor $orderProcessor
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        Order\Converter $converter,
        Order\Processor $orderProcessor,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->converter = $converter;
        $this->orderProcessor = $orderProcessor;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $file) {
            $fileName = $this->fixtureManager->getFixture($file);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            $isFirst = true;
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;
                if ($isFirst) {
                    $customer = $this->customerRepository->get($row['customer_email']);
                    if (!$customer->getId()) {
                        continue;
                    }
                    /** @var \Magento\Sales\Model\ResourceModel\Collection $orderCollection */
                    $orderCollection = $this->orderCollectionFactory->create();
                    $orderCollection->addFilter('customer_id', $customer->getId());
                    if ($orderCollection->count() > 0) {
                        break;
                    }
                }
                $isFirst = false;
                $orderData = $this->converter->convertRow($row);
                $this->orderProcessor->createOrder($orderData);
            }
        }
    }
}
