<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Console\Command;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class ProductAttributesCleanUp
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductAttributesCleanUp extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute
     */
    protected $attributeResource;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata
     */
    protected $metadata;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeResource = $attributeResource;
        $this->appState = $appState;
        $this->metadata = $metadataPool->getMetadata(ProductInterface::class);
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('catalog:product:attributes:cleanup');
        $this->setDescription('Removes unused product attributes.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setDecorated(true);
        $this->appState->setAreaCode('catalog');
        $connection = $this->attributeResource->getConnection();
        $attributeTables = $this->getAttributeTables();

        $progress = new \Symfony\Component\Console\Helper\ProgressBar($output, count($attributeTables));
        $progress->setFormat('<comment>%message%</comment> %current%/%max% [%bar%] %percent:3s%% %elapsed%');

        $this->attributeResource->beginTransaction();
        try {
            // Find and remove unused attributes
            foreach ($attributeTables as $attributeTable) {
                $progress->setMessage($attributeTable . ' ');
                $affectedIds = $this->getAffectedAttributeIds($connection, $attributeTable);
                if (count($affectedIds) > 0) {
                    $connection->delete($attributeTable, ['value_id in (?)' => $affectedIds]);
                }
                $progress->advance();
            }
            $this->attributeResource->commit();

            $output->writeln("");
            $output->writeln("<info>Unused product attributes successfully cleaned up:</info>");
            $output->writeln("<comment>  " . implode("\n  ", $attributeTables) . "</comment>");
        } catch (\Exception $exception) {
            $this->attributeResource->rollBack();

            $output->writeln("");
            $output->writeln("<error>{$exception->getMessage()}</error>");
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeTables()
    {
        $searchResult = $this->productAttributeRepository->getList($this->searchCriteriaBuilder->create());
        $attributeTables = [];

        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $productAttribute */
        foreach ($searchResult->getItems() as $productAttribute) {
            $attributeTable = $productAttribute->getBackend()->getTable();
            if (!in_array($attributeTable, $attributeTables)
                && $attributeTable != $this->attributeResource->getTable('catalog_product_entity')
            ) {
                $attributeTables[] = $attributeTable;
            }
        }
        return $attributeTables;
    }

    /**
     * @param AdapterInterface $connection
     * @param string $attributeTableName
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function getAffectedAttributeIds(AdapterInterface $connection, $attributeTableName)
    {
        $linkField = $this->metadata->getLinkField();
        $select = $connection->select()->reset();
        $select->from(['e' => $this->attributeResource->getTable('catalog_product_entity')], 'ei.value_id');
        $select->join(
            ['ei' => $attributeTableName],
            'ei.' . $linkField . ' = e.' . $linkField . ' AND ei.store_id != 0',
            ''
        );
        $select->join(['s' => $this->attributeResource->getTable('store')], 's.store_id = ei.store_id', '');
        $select->join(['sg' => $this->attributeResource->getTable('store_group')], 'sg.group_id = s.group_id', '');
        $select->joinLeft(
            ['pw' => $this->attributeResource->getTable('catalog_product_website')],
            'pw.website_id = sg.website_id AND pw.product_id = e.entity_id',
            ''
        );
        $select->where('pw.product_id is null');
        return $connection->fetchCol($select);
    }
}
