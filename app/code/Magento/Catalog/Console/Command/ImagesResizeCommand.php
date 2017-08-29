<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Console\Command;

use Magento\Catalog\Console\ImageResizeOptions as Options;
use Magento\Catalog\Model\Product\Image\Process\QueueFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;

class ImagesResizeCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Image\CacheFactory
     */
    protected $imageCacheFactory;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\Product\Image\CacheFactory $imageCacheFactory
     * @param null|QueueFactory $queueFactory
     * @param null|Options $options
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Product\Image\CacheFactory $imageCacheFactory,
        $queueFactory = null,
        $options = null
    ) {
        $this->appState = $appState;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->imageCacheFactory = $imageCacheFactory;

        $this->queueFactory = $queueFactory ?: ObjectManager::getInstance()->get(QueueFactory::class);
        $this->options = $options ?: ObjectManager::getInstance()->get(Options::class);

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('catalog:images:resize')
            ->setDescription('Creates resized product images')
            ->setDefinition($this->options->getOptionsList());
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $options = $input->getOptions();
        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productIds = $productCollection->getAllIds();
        $productCount = count($productIds);
        $limit = $this->getLimit($options);
        $offset = $this->getOffset($options);

        if ($limit) {
            $productIds = array_slice($productIds, $offset, $limit);
        }

        $maxOffset = $productCount - 1;
        if ($offset && $offset > $maxOffset) {
            $output->writeln("<error>Offset may not be higher than $maxOffset</error>");
            return Cli::RETURN_FAILURE;
        }

        if (!count($productIds)) {
            $output->writeln("<info>No product images to resize</info>");
            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_SUCCESS;
        }

        $processes = $this->getProcessesAmount($options);
        /** @var \Magento\Catalog\Model\Product\Image\Process\Queue $queue */
        $queue = $this->queueFactory->create(
            [
                'maxProcesses' => $processes,
                'output' => $output,
            ]
        );

        try {
            $queue->setProducts($productIds);
            $output->writeln("<info>Resizing product images</info>");
            $queue->process();
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }

        $output->write("\n");
        $output->writeln("<info>Product images resized successfully</info>");
        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param array $options
     * @return int
     */
    private function getProcessesAmount(array $options)
    {
        return isset($options[Options::JOBS_AMOUNT])
            ? (int)$options[Options::JOBS_AMOUNT]
            : Options::DEFAULT_JOBS_AMOUNT;
    }

    /**
     * @param array $options
     * @return int
     */
    private function getLimit(array $options)
    {
        return isset($options[Options::PRODUCT_LIMIT])
            ? (int)$options[Options::PRODUCT_LIMIT]
            : Options::DEFAULT_PRODUCT_LIMIT;
    }

    /**
     * @param array $options
     * @return int
     */
    private function getOffset(array $options)
    {
        return isset($options[Options::PRODUCT_OFFSET])
            ? (int)$options[Options::PRODUCT_OFFSET]
            : Options::DEFAULT_PRODUCT_OFFSET;
    }
}
