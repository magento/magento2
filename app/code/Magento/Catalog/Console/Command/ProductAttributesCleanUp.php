<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.1.0
 */
class ProductAttributesCleanUp extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     * @since 2.1.0
     */
    protected $productAttributeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     * @since 2.1.0
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute
     * @since 2.1.0
     */
    protected $attributeResource;

    /**
     * @var \Magento\Framework\App\State
     * @since 2.1.0
     */
    protected $appState;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata
     * @since 2.1.0
     */
    protected $metadata;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @since 2.1.0
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
     * @since 2.1.0
     */
    protected function configure()
    {
        $this->setName('catalog:product:attributes:cleanup');
        $this->setDescription('Removes unused product attributes.');
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setDecorated(true);
        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
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
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $this->attributeResource->rollBack();

            $output->writeln("");
            $output->writeln("<error>{$exception->getMessage()}</error>");
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.1.0
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
     * @since 2.1.0
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
