<?php
declare(strict_types=1);

namespace Chechur\Blog\Model\Post;

use Chechur\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Chechur\Blog\Model\ResourceModel\Post\Collection|void
     */
    protected $collection;

    /**
     * @var
     */
    protected $_loadedData;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * DataProvider constructor.
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param CollectionFactory $postCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $postCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $postCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
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
            if ($action->getImage()) {
                $m['image'][0]['name'] = $action->getImage();
                $m['image'][0]['url'] = $this->getMediaUrl() . $action->getImage();
                $fullData = $this->_loadedData;
                $this->_loadedData[$action->getId()] = array_merge($fullData[$action->getId()], $m);
            }
        }

        $data = $this->dataPersistor->get('chechur_blog_post_form_data_source');

        if (!empty($data)) {
            $action = $this->collection->getNewEmptyItem();
            $action->setData($data);
            $this->_loadedData[$action->getId()] = $action->getData();
            $this->dataPersistor->clear('chechur_blog_post_form_data_source');
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
