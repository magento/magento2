<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Console\Command;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\App\ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\ObjectManagerFactory $objectManagerFactory
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeResource = $attributeResource;
        $this->appState = $appState;
        $this->objectManager = $objectManagerFactory->create([]);
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

        /** @var \Symfony\Component\Console\Helper\ProgressBar $progress */
        $progress = $this->objectManager->create(
            'Symfony\Component\Console\Helper\ProgressBar',
            ['output' => $output, 'max' => count($attributeTables)]
        );
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
        $connection = $this->attributeResource->getConnection();
        $searchResult = $this->productAttributeRepository->getList($this->searchCriteriaBuilder->create());
        $attributeTables = [];

        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $productAttribute */
        foreach ($searchResult->getItems() as $productAttribute) {
            if (!in_array($productAttribute->getBackend()->getTable(), $attributeTables)
                && $productAttribute->getBackend()->getTable() != $connection->getTableName('catalog_product_entity')
            ) {
                $attributeTables[] = $productAttribute->getBackend()->getTable();
            }
        }
        return $attributeTables;
    }

    /**
     * @param AdapterInterface $connection
     * @param string $attributeTableName
     * @return array
     */
    private function getAffectedAttributeIds(AdapterInterface $connection, $attributeTableName)
    {
        $select = $connection->select()->reset();
        $select->from(['e' => $connection->getTableName('catalog_product_entity')], 'ei.value_id');
        $select->join(['ei' => $attributeTableName], 'ei.entity_id = e.entity_id AND ei.store_id != 0', '');
        $select->join(['s' => $connection->getTableName('store')], 's.store_id = ei.store_id', '');
        $select->join(['sg' => $connection->getTableName('store_group')], 'sg.group_id = s.group_id', '');
        $select->joinLeft(
            ['pw' => $connection->getTableName('catalog_product_website')],
            'pw.website_id = sg.website_id AND pw.product_id = e.entity_id',
            ''
        );
        $select->where('pw.product_id is null');
        return $connection->fetchCol($select);
    }
}
