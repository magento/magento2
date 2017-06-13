<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image\Process;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\CacheFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Resize Queue
 *
 * Resize catalog images in parallel forks (if available)
 */
class Queue
{
    /**
     * Default max amount of processes
     */
    const DEFAULT_MAX_PROCESSES_AMOUNT = 1;

    /**
     * Resize state identifier for "resized" state
     */
    const STATE_RESIZED = 1;

    /**
     * @var array
     */
    protected $products = [];

    /**
     * @var int[]
     */
    protected $processIds = [];

    /**
     * @var int[]
     */
    protected $inProgress = [];

    /**
     * @var int
     */
    protected $maxProcesses;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var CacheFactory
     */
    protected $imageCacheFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var int
     */
    protected $start = 0;

    /**
     * @var array
     */
    protected $state = [];

    /**
     * @var int
     */
    protected $lastJobStarted = 0;

    /**
     * @var Product\Image\Cache
     */
    protected $imageCache;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ResourceConnection $resourceConnection
     * @param CacheFactory $imageCacheFactory
     * @param OutputInterface $output
     * @param array $options
     * @param int $maxProcesses
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resourceConnection,
        CacheFactory $imageCacheFactory,
        OutputInterface $output,
        array $options = [],
        $maxProcesses = self::DEFAULT_MAX_PROCESSES_AMOUNT
    ) {
        $this->productRepository = $productRepository;
        $this->resourceConnection = $resourceConnection;
        $this->output = $output;
        $this->imageCacheFactory = $imageCacheFactory;
        $this->options = $options;
        $this->maxProcesses = $maxProcesses;
        $this->imageCache = $this->imageCacheFactory->create();
    }

    /**
     * @param array $productIds
     */
    public function setProducts(array $productIds)
    {
        $this->products = array_combine($productIds, $productIds);
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Process jobs
     *
     * @return int
     */
    public function process()
    {
        $this->imageCache->getData();
        $returnStatus = 0;
        $this->start = $this->lastJobStarted = time();
        $products = $this->products;
        while (count($products)) {
            foreach ($products as $productId) {
                $this->assertAndExecute($productId, $products);
            }
            usleep(20000);
            foreach ($this->inProgress as $productId) {
                if ($this->isResized($productId)) {
                    unset($this->inProgress[$productId]);
                }
            }
        }

        $this->awaitForAllProcesses();

        return $returnStatus;
    }

    /**
     * Check that we are not over max processes and execute
     *
     * @param int $productId
     * @param array $products
     * @return void
     */
    public function assertAndExecute($productId, array & $products)
    {
        if ($this->maxProcesses < 2 || (count($this->inProgress) < $this->maxProcesses)) {
            unset($products[$productId]);
            $this->execute($productId);
        }
    }

    /**
     * Wait untill all processes are finished
     *
     * @return void
     */
    public function awaitForAllProcesses()
    {
        while ($this->inProgress) {
            foreach ($this->inProgress as $productId => $product) {
                if ($this->isResized($product)) {
                    unset($this->inProgress[$productId]);
                }
            }
            usleep(100000);
        }
        if ($this->isCanBeParalleled()) {
            // close connections only if ran with forks
            $this->resourceConnection->closeConnection();
        }
    }

    /**
     * @return bool
     */
    public function isCanBeParalleled()
    {
        return function_exists('pcntl_fork') && $this->maxProcesses > 1;
    }

    /**
     * @param int $productId
     * @return bool true on success for main process and exit for child process
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function execute(int $productId)
    {
        $this->lastJobStarted = time();

        if ($this->isCanBeParalleled()) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                throw new \RuntimeException('Unable to fork a new process');
            }

            if ($pid) {
                $this->inProgress[$productId] = $productId;
                $this->processIds[$productId] = $pid;
                return true;
            }

            try {
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->productRepository->getById($productId);
                $product->getMediaGalleryImages();
            } catch (NoSuchEntityException $e) {
                exit();
            }

            // process child process
            $this->inProgress = [];
            $this->imageCache->generate($product);
            $this->output->write('.');
            exit();
        }

        try {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId);
            $product->getMediaGalleryImages();
        } catch (NoSuchEntityException $e) {
            return true;
        }

        $this->imageCache->generate($product);
        $this->output->write('.');
        return true;
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function isResized($productId)
    {
        if ($this->isCanBeParalleled()) {
            if ($this->getState($productId) === null) {
                $pid = pcntl_waitpid($this->getPid($productId), $status, WNOHANG);
                if ($pid === $this->getPid($productId)) {
                    $this->setState($productId, self::STATE_RESIZED);

                    unset($this->inProgress[$productId]);
                    return pcntl_wexitstatus($status) === 0;
                }
                return false;
            }

        }
        return $this->getState($productId);
    }

    /**
     * @param int $productId
     * @return null|int
     */
    public function getState($productId)
    {
        return isset($this->state[$productId]) ?: null;
    }

    /**
     * @param int $productId
     * @param int $state
     * @return null|int
     */
    public function setState($productId, $state)
    {
        return $this->state[$productId] = $state;
    }

    /**
     * @param int $productId
     * @return int|null
     */
    public function getPid($productId)
    {
        return isset($this->processIds[$productId])
            ? $this->processIds[$productId]
            : null;
    }

    /**
     * Free resources
     *
     * Protect against zombie process
     *
     * @return void
     */
    public function __destruct()
    {
        foreach ($this->inProgress as $productId) {
            if (pcntl_waitpid($this->getPid($productId), $status) === -1) {
                throw new \RuntimeException(
                    'Error while waiting for image resize for product ID: ' . $this->getPid($productId)
                    . '; Status: ' . $status
                );
            }
        }
    }
}
