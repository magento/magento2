<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image\Process;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\CacheFactory;
use Magento\Framework\App\ResourceConnection;
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
     * @var \Magento\Catalog\Model\Product[]
     */
    protected $inProgress = [];

    /**
     * @var int
     */
    protected $maxProcesses;

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
     * @param ResourceConnection $resourceConnection
     * @param CacheFactory $imageCacheFactory
     * @param OutputInterface $output
     * @param array $options
     * @param int $maxProcesses
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CacheFactory $imageCacheFactory,
        OutputInterface $output,
        array $options = [],
        $maxProcesses = self::DEFAULT_MAX_PROCESSES_AMOUNT
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->output = $output;
        $this->imageCacheFactory = $imageCacheFactory;
        $this->options = $options;
        $this->maxProcesses = $maxProcesses;
        $this->imageCache = $this->imageCacheFactory->create();
    }

    /**
     * @param Product $product
     * @return bool true on success
     */
    public function add(Product $product)
    {
        $this->products[$product->getId()] = $product;
        return true;
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
            foreach ($products as $id => $product) {
                $this->assertAndExecute($id, $products, $product);
            }
            usleep(20000);
            foreach ($this->inProgress as $id => $product) {
                if ($this->isResized($product)) {
                    unset($this->inProgress[$id]);
                }
            }
        }

        $this->awaitForAllProcesses();

        return $returnStatus;
    }

    /**
     * Check that we are not over max processes and execute
     *
     * @param int $id
     * @param array $products
     * @param Product $product
     * @return void
     */
    protected function assertAndExecute($id, array & $products, Product $product)
    {
        if ($this->maxProcesses < 2 || (count($this->inProgress) < $this->maxProcesses)) {
            unset($products[$id]);
            $this->execute($product);
        }
    }

    /**
     * Wait untill all processes are finished
     *
     * @return void
     */
    protected function awaitForAllProcesses()
    {
        while ($this->inProgress) {
            foreach ($this->inProgress as $id => $product) {
                if ($this->isResized($product)) {
                    unset($this->inProgress[$id]);
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
    protected function isCanBeParalleled()
    {
        return function_exists('pcntl_fork') && $this->maxProcesses > 1;
    }

    /**
     * @param Product $product
     * @return bool true on success for main process and exit for child process
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    protected function execute(Product $product)
    {
        $this->lastJobStarted = time();

        if ($this->isCanBeParalleled()) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                throw new \RuntimeException('Unable to fork a new process');
            }

            if ($pid) {
                $this->inProgress[$product->getId()] = $product;
                $this->processIds[$product->getId()] = $pid;
                return true;
            }

            // process child process
            $this->inProgress = [];
            $this->imageCache->generate($product);
            $this->output->write('.');
            exit();
        } else {
            $this->imageCache->generate($product);
            return true;
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function isResized($product)
    {
        if ($this->isCanBeParalleled()) {
            if ($this->getState($product) === null) {
                $pid = pcntl_waitpid($this->getPid($product), $status, WNOHANG);
                if ($pid === $this->getPid($product)) {
                    $this->setState($product, self::STATE_RESIZED);

                    unset($this->inProgress[$product->getId()]);
                    return pcntl_wexitstatus($status) === 0;
                }
                return false;
            }

        }
        return $this->getState($product);
    }

    /**
     * @param Product $product
     * @return null|int
     */
    protected function getState(Product $product)
    {
        return isset($this->state[$product->getId()]) ?: null;
    }

    /**
     * @param Product $product
     * @param int $state
     * @return null|int
     */
    protected function setState(Product $product, $state)
    {
        return $this->state[$product->getId()] = $state;
    }

    /**
     * @param Product $product
     * @return int|null
     */
    protected function getPid(Product $product)
    {
        return isset($this->processIds[$product->getId()])
            ? $this->processIds[$product->getId()]
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
        foreach ($this->inProgress as $product) {
            if (pcntl_waitpid($this->getPid($product), $status) === -1) {
                throw new \RuntimeException(
                    'Error while waiting for image resize for product: ' . $this->getPid($product)
                    . '; Status: ' . $status
                );
            }
        }
    }
}
