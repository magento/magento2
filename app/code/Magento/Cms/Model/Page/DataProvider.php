<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Page;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var AuthorizationInterface
     */
    private $auth;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CustomLayoutManagerInterface
     */
    private $customLayoutManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $pageCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     * @param AuthorizationInterface|null $auth
     * @param RequestInterface|null $request
     * @param CustomLayoutManagerInterface|null $customLayoutManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $pageCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null,
        ?AuthorizationInterface $auth = null,
        ?RequestInterface $request = null,
        ?CustomLayoutManagerInterface $customLayoutManager = null
    ) {
        $this->collection = $pageCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->auth = $auth ?? ObjectManager::getInstance()->get(AuthorizationInterface::class);
        $this->meta = $this->prepareMeta($this->meta);
        $this->request = $request ?? ObjectManager::getInstance()->get(RequestInterface::class);
        $this->customLayoutManager = $customLayoutManager
            ?? ObjectManager::getInstance()->get(CustomLayoutManagerInterface::class);
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $page \Magento\Cms\Model\Page */
        foreach ($items as $page) {
            $this->loadedData[$page->getId()] = $page->getData();
            if ($page->getCustomLayoutUpdateXml()) {
                //Deprecated layout update exists.
                $this->loadedData[$page->getId()]['layout_update_selected'] = '_existing_';
            }
        }

        $data = $this->dataPersistor->get('cms_page');
        if (!empty($data)) {
            $page = $this->collection->getNewEmptyItem();
            $page->setData($data);
            $this->loadedData[$page->getId()] = $page->getData();
            if ($page->getCustomLayoutUpdateXml()) {
                $this->loadedData[$page->getId()]['layout_update_selected'] = '_existing_';
            }
            $this->dataPersistor->clear('cms_page');
        }

        return $this->loadedData;
    }

    /**
     * @inheritDoc
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        if (!$this->auth->isAllowed('Magento_Cms::save_design')) {
            $designMeta = [
                'design' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'disabled' => true
                            ]
                        ]
                    ]
                ],
                'custom_design_update' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'disabled' => true
                            ]
                        ]
                    ]
                ]
            ];
            $meta = array_merge_recursive($meta, $designMeta);
        }

        //List of custom layout files available for current page.
        $options = [['label' => 'No update', 'value' => '']];
        if ($this->getRequestFieldName() && ($pageId = (int)$this->request->getParam($this->getRequestFieldName()))) {
            //We must have a specific page selected.
            //Finding our page.
            $found  = null;
            /** @var \Magento\Cms\Model\Page  $page */
            foreach ($this->collection->getItems() as $page) {
                if ($page->getId() == $pageId) {
                    $found = $page;
                    break;
                }
            }
            //If custom layout XML is set then displaying this special option.
            if ($found) {
                if ($found->getCustomLayoutUpdateXml()) {
                    $options[] = ['label' => 'Use existing layout update XML', 'value' => '_existing_'];
                }
                foreach ($this->customLayoutManager->fetchAvailableFiles($found) as $layoutFile) {
                    $options[] = ['label' => $layoutFile, 'value' => $layoutFile];
                }
            }
        }
        $customLayoutMeta = [
            'design' => [
                'children' => [
                    'custom_layout_update_select' => [
                        'arguments' => [
                            'data' => ['options' => $options]
                        ]
                    ]
                ]
            ]
        ];
        $meta = array_merge_recursive($meta, $customLayoutMeta);

        return $meta;
    }
}
