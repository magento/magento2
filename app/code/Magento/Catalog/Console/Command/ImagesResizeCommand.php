<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\Product\Image\CacheFactory $imageCacheFactory
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Product\Image\CacheFactory $imageCacheFactory
    ) {
        $this->appState = $appState;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->imageCacheFactory = $imageCacheFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('catalog:images:resize')
            ->setDescription('Creates resized product images');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productIds = $productCollection->getAllIds();
        if (!count($productIds)) {
            $output->writeln("<info>No product images to resize</info>");
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        }

        $errorMessage = '';
        try {
            foreach ($productIds as $productId) {
                try {
                    /** @var \Magento\Catalog\Model\Product $product */
                    $product = $this->productRepository->getById($productId);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    continue;
                }

                try {
                    /** @var \Magento\Catalog\Model\Product\Image\Cache $imageCache */
                    $imageCache = $this->imageCacheFactory->create();
                    $imageCache->generate($product);
                } catch (\Magento\Framework\Exception\RuntimeException $e) {
                    $errorMessage = $e->getMessage();
                }

                $output->write(".");
            }
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $output->write("\n");
        $output->writeln("<info>Product images resized successfully.</info>");

        if ($errorMessage !== '') {
            $output->writeln("<comment>{$errorMessage}</comment>");
        }

        return 0;
    }
}
