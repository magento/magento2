<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image\Process;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\Process\CacheFactory;
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
    private $products = [];

    /**
     * @var int[]
     */
    private $processIds = [];

    /**
     * @var int[]
     */
    private $inProgress = [];

    /**
     * @var int
     */
    private $maxProcesses;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var CacheFactory
     */
    private $imageCacheFactory;

    /**
     * @var int
     */
    private $start = 0;

    /**
     * @var array
     */
    private $state = [];

    /**
     * @var int
     */
    private $lastJobStarted = 0;

    /**
     * @var Product\Image\Process\Cache
     */
    private $imageCache;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ResourceConnection $resourceConnection
     * @param CacheFactory $imageCacheFactory
     * @param OutputInterface $output
     * @param int $maxProcesses
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resourceConnection,
        CacheFactory $imageCacheFactory,
        OutputInterface $output,
        $maxProcesses = self::DEFAULT_MAX_PROCESSES_AMOUNT
    ) {
        $this->productRepository = $productRepository;
        $this->resourceConnection = $resourceConnection;
        $this->output = $output;
        $this->imageCacheFactory = $imageCacheFactory;
        $this->maxProcesses = $maxProcesses;
    }

    /**
     * @param array $productIds
     * @return Queue
     */
    public function setProducts(array $productIds)
    {
        $this->products = array_combine($productIds, $productIds);
        return $this;
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
        $this->imageCache = $this->imageCacheFactory->create();
        if ($this->isCanBeParalleled()) {
            $this->imageCache->preloadData();
        }
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
    private function assertAndExecute($productId, array & $products)
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
    private function awaitForAllProcesses()
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
    private function isCanBeParalleled()
    {
        return function_exists('pcntl_fork') && $this->maxProcesses > 1;
    }

    /**
     * @param int $productId
     * @return bool true on success for main process and exit for child process
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function execute(int $productId)
    {
        $this->lastJobStarted = time();
        $isCanBeParalleled = $this->isCanBeParalleled();

        if ($isCanBeParalleled) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                throw new \RuntimeException('Unable to fork a new process');
            }

            if ($pid) {
                $this->inProgress[$productId] = $productId;
                $this->processIds[$productId] = $pid;
                return true;
            }
        }

        try {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId);
            $product->getMediaGalleryImages();
        } catch (NoSuchEntityException $e) {
            if ($isCanBeParalleled) {
                exit();
            }
            return true;
        }

        if ($isCanBeParalleled) {
            // process child process
            $this->inProgress = [];
        }
        $this->imageCache->generate($product);
        $this->output->write('.');
        if ($isCanBeParalleled) {
            exit();
        }
        return true;
    }

    /**
     * @param int $productId
     * @return bool
     */
    private function isResized($productId)
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
    private function getState($productId)
    {
        return isset($this->state[$productId]) ?: null;
    }

    /**
     * @param int $productId
     * @param int $state
     * @return null|int
     */
    private function setState($productId, $state)
    {
        return $this->state[$productId] = $state;
    }

    /**
     * @param int $productId
     * @return int|null
     */
    private function getPid($productId)
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
