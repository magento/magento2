<?php
/**
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Store\Model;


use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data;

class StoreRepository implements \Magento\Store\Api\StoreRepositoryInterface
{
    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Magento\Store\Model\Resource\Store\CollectionFactory
     */
    protected $storeCollectionFactory;

    /**
     * @var Data\StoreInterface[]
     */
    protected $entities = [];

    /**
     * @var Data\StoreInterface[]
     */
    protected $entitiesById = [];

    /**
     * @var bool
     */
    protected $allLoaded = false;

    /**
     * @param StoreFactory $storeFactory
     * @param \Magento\Store\Model\Resource\Store\CollectionFactory $storeFactory
     */
    public function __construct(
        StoreFactory $storeFactory,
        \Magento\Store\Model\Resource\Store\CollectionFactory $storeCollectionFactory
    ) {
        $this->storeFactory = $storeFactory;
        $this->storeCollectionFactory = $storeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($code)
    {
        if (isset($this->entities[$code])) {
            return $this->entities[$code];
        }
        $store = $this->storeFactory->create();
        $store->load($code, 'code');
        if ($store->getId() === null) {
            // TODO: MAGETWO-39826
            throw new \InvalidArgumentException();
        }
        $this->entities[$code] = $store;
        $this->entitiesById[$store->getId()] = $store;
        return $store;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        if (isset($this->entitiesById[$id])) {
            return $this->entitiesById[$id];
        }
        $store = $this->storeFactory->create();
        $store->load($id);
        if ($store->getId() === null) {
            throw new NoSuchEntityException();
        }
        $this->entitiesById[$id] = $store;
        $this->entities[$store->getCode()] = $store;
        return $store;
    }

    /**
     * @return Data\StoreInterface[]
     */
    public function getList()
    {
        if (!$this->allLoaded) {
            /** @var $storeCollection \Magento\Store\Model\Resource\Store\Collection */
            $storeCollection = $this->storeCollectionFactory->create();
            $storeCollection->setLoadDefault(true);
            foreach ($storeCollection as $item) {
                $this->entities[$item->getCode()] = $item;
                $this->entitiesById[$item->getId()] = $item;
            }
            $this->allLoaded = true;
        }
        return $this->entities;
    }

    /**
     * {@inheritdoc}
     */
    public function clean()
    {
        $this->entities = [];
        $this->entitiesById = [];
        $this->allLoaded = false;
    }
}
