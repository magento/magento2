<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Fixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\Downloadable\Api\DomainManagerInterface;

class DownloadableProduct extends Product
{
    private const DEFAULT_DATA = [
        'type_id' => Type::TYPE_DOWNLOADABLE,
        'name' => 'DownloadableProduct%uniqid%',
        'sku' => 'downloadable-product%uniqid%',
        'price' => 0.00,
        'links_purchased_separately' => 1,
        'links_title' => 'Downloadable Links%uniqid%',
        'links_exist' => 0,
        'extension_attributes' => [
            'website_ids' => [1],
            'stock_item' => [
                'use_config_manage_stock' => true,
                'qty' => 100,
                'is_qty_decimal' => false,
                'is_in_stock' => true,
            ],
            'downloadable_product_links' => [],
            'downloadable_product_samples' => null
        ],
    ];

    private const DOMAINS = ['example.com','www.example.com'];

    /**
     * DownloadableProduct constructor
     *
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
     * @param DataMerger $dataMerger
     * @param ProductRepositoryInterface $productRepository
     * @param DirectoryList $directoryList
     * @param Link $link
     * @param File $file
     */
    public function __construct(
        private readonly ServiceFactory $serviceFactory,
        private readonly ProcessorInterface $dataProcessor,
        private readonly DataMerger $dataMerger,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly DirectoryList $directoryList,
        private readonly Link $link,
        private readonly File $file,
        private readonly DomainManagerInterface $domainManager
    ) {
        parent::__construct($serviceFactory, $dataProcessor, $dataMerger, $productRepository);
    }

    /**
     * @inheritdoc
     *
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function apply(array $data = []): ?DataObject
    {
        $this->domainManager->addDomains(self::DOMAINS);

        return parent::apply($this->prepareData($data));
    }

    public function revert(DataObject $data): void
    {
        $this->domainManager->removeDomains(self::DOMAINS);
        parent::revert($data);
    }

    /**
     * Prepare product data
     *
     * @param array $data
     * @return array
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function prepareData(array $data): array
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data);

        // Remove common properties not needed for downloadable products
        unset($data['weight']);

        // Prepare downloadable links
        $links = $this->prepareLinksData($data);
        $data['extension_attributes']['downloadable_product_links'] = $links;
        $data['links_exist'] = count($links);

        return $this->dataProcessor->process($this, $data);
    }

    /**
     * Prepare links data
     *
     * @param array $data
     * @return array
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function prepareLinksData(array $data): array
    {
        $links = [];
        foreach ($data['extension_attributes']['downloadable_product_links'] as $link) {

            if ($link['link_type'] == 'url') {
                $link['link_url'] = 'http://example.com/downloadable.txt';
                $link['link_file'] = '';
            } else {
                $link['link_file'] = $this->generateDownloadableLink($link['link_file'] ?? 'test-' . uniqid() . '.txt');
                $link['link_url'] = '';
            }

            $links[] = [
                'id' => null,
                'title' => $link['title'] ?? 'Test Link%uniqid%',
                'price' => $link['price'] ?? 0,
                'link_type' => $link['link_type'] ?? 'file',
                'link_url' => $link['link_url'],
                'link_file' => $link['link_file'],
                'is_shareable' => $link['is_shareable'] ?? 0,
                'number_of_downloads' => $link['number_of_downloads'] ?? 5,
                'sort_order' => $link['sort_order'] ?? 10,
            ];
        }

        return $links;
    }

    /**
     * Generate downloadable link file
     *
     * @param string $fileName
     * @return string
     * @throws FileSystemException|LocalizedException
     */
    public function generateDownloadableLink(string $fileName): string
    {
        try {
            $subDir = sprintf('%s/%s', $fileName[0], $fileName[1]);
            $mediaPath = sprintf(
                '%s/%s/%s',
                $this->directoryList->getPath(DirectoryList::MEDIA),
                $this->link->getBasePath(),
                $subDir
            );
            $this->file->checkAndCreateFolder($mediaPath);
            $this->file->write(sprintf('%s/%s', $mediaPath, $fileName), "This is a temporary text file.");

            return sprintf('/%s/%s', $subDir, $fileName);
        } catch (FileSystemException $e) {
            throw new FileSystemException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
