<?php
namespace Smetana\Third\Model\Partner;

use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Smetana\Third\Model\ResourceModel\Partner\CollectionFactory as PartnerCollectionFactory;

/**
 * Class Partner form DataProvider
 *
 * @package Smetana\Third\Model\Partner
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * Edit Form data
     *
     * @var array
     */
    private $loadedData = [];

    /**
     * Partner collection factory
     *
     * @var PartnerCollectionFactory
     */
    private $collectionFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param PartnerCollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        PartnerCollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get form data
     *
     * @return array
     */
    public function getData(): array
    {
        if (empty($this->loadedData)) {
            foreach ($this->collectionFactory->create() as $partner) {
                $this->loadedData[$partner->getPartnerId()] = $partner->getData();
            }
        }

        return $this->loadedData;
    }

    /**
     * Filtering
     *
     * @param Filter $filter
     *
     * @return null
     */
    public function addFilter(Filter $filter)
    {
        return null;
    }
}
