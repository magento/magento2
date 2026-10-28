<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Cms\Model\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page\Service\PageService;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use Psr\Log\LoggerInterface;

/**
 * Cms Page DataProvider
 */
class DataProvider extends ModifierPoolDataProvider
{
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
     * @var PageService
     */
    private $pageService;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
     * @param PageService|null $pageService
     * @param LoggerInterface|null $logger
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
        ?PoolInterface $pool = null,
        ?AuthorizationInterface $auth = null,
        ?RequestInterface $request = null,
        ?PageService $pageService = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->collection = $pageCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->auth = $auth ?? ObjectManager::getInstance()->get(AuthorizationInterface::class);
        $this->meta = $this->prepareMeta($this->meta);
        $this->request = $request ?? ObjectManager::getInstance()->get(RequestInterface::class);
        $this->pageService = $pageService ?: ObjectManager::getInstance()->get(PageService::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
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

        $page = $this->getCurrentPage();
        $pageId = $page->getId() ?? '';
        $this->loadedData[$pageId] = $page->getData();
        if ($page->getCustomLayoutUpdateXml() || $page->getLayoutUpdateXml()) {
            //Deprecated layout update exists.
            $this->loadedData[$pageId]['layout_update_selected'] = '_existing_';
        }

        return $this->loadedData;
    }

    /**
     * Return current page
     *
     * @return PageInterface
     */
    private function getCurrentPage(): PageInterface
    {
        $pageId = $this->getPageId();
        if ($pageId) {
            return $this->pageService->getPageById($pageId);
        }

        $data = $this->dataPersistor->get('cms_page');
        if (empty($data)) {
            return $this->pageService->createPageFactory();
        }
        $this->dataPersistor->clear('cms_page');

        return $this->pageService->createPageFactory()
            ->setData($data);
    }

    /**
     * Returns current page id from request
     *
     * @return int
     */
    private function getPageId(): int
    {
        return (int) $this->request->getParam($this->getRequestFieldName());
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
        $options = [['label' => 'No update', 'value' => '_no_update_']];

        $page = null;
        try {
            $pageId = $this->getPageId();
            if ($pageId) {
                $page = $this->pageService->getPageById($pageId);
                if ($page->getCustomLayoutUpdateXml() || $page->getLayoutUpdateXml()) {
                    $options[] = ['label' => 'Use existing layout update XML', 'value' => '_existing_'];
                }
                foreach ($this->pageService->fetchAvailableCustomLayouts($page) as $layoutFile) {
                    $options[] = ['label' => $layoutFile, 'value' => $layoutFile];
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
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
