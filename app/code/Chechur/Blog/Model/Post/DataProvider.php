<?php
declare(strict_types=1);

namespace Chechur\Blog\Model\Post;

use Chechur\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterfacer;
use Magento\Framework\ObjectManager\ObjectManager;
use Chechur\Blog\Model\Post\FileInfo;
use Magento\Framework\Filesystem;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Chechur\Blog\Model\ResourceModel\Post\Collection|void
     */
    protected $collection;

    private $dataPersistor;

    protected $_loadedData;

    /**
     * DataProvider constructor.
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param CollectionFactory $postCollectionFactory
     * @param DataPersistorInterfacer $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $postCollectionFactory,
        DataPersistorInterfacer $dataPersistor,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $postCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }

        $items = $this->collection->getItems();


        foreach ($items as $action) {
            $this->_loadedData[$action->getId()]['contact'] = $action->getData();
        }

        return $this->_loadedData;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'post/tmp/image/';
        return $mediaUrl;
    }

}
