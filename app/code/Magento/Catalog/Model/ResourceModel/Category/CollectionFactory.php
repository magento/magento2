<?php
namespace Magento\Catalog\Model\ResourceModel\Category;

/**
 * Factory class for category collection
 */
class CollectionFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Catalog category flat state
     *
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    private $catalogCategoryFlatState;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $catalogCategoryFlatState
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $catalogCategoryFlatState
    ) {
        $this->objectManager = $objectManager;
        $this->catalogCategoryFlatState = $catalogCategoryFlatState;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function create(array $data = array())
    {
        if ($this->catalogCategoryFlatState->isAvailable()) {
            return $this->objectManager->create(
                \Magento\Catalog\Model\ResourceModel\Category\Flat\Collection::class,
                $data
            );
        }
        return $this->objectManager->create(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class,
            $data
        );
    }
}
