<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Index;

use Magento\Backend\Model\Search\ItemFactory;
use Magento\Backend\Model\Search\SearchCriteria;
use Magento\Backend\Model\Search\SearchCriteriaFactory;

/**
 * @api
 */
class GlobalSearch extends \Magento\Backend\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Search modules list
     *
     * @var array
     */
    protected $_searchModules;

    /**
     * modules that support preview
     *
     * @var array
     */
    private $previewModules;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var SearchCriteriaFactory
     */
    private $criteriaFactory;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param ItemFactory $itemFactory
     * @param SearchCriteriaFactory $criteriaFactory
     * @param array $searchModules
     * @param array $previewModules
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        ItemFactory $itemFactory,
        SearchCriteriaFactory $criteriaFactory,
        array $searchModules = [],
        array $previewModules = []
    ) {
        $this->itemFactory = $itemFactory;
        $this->criteriaFactory = $criteriaFactory;
        $this->_searchModules = $searchModules;
        $this->previewModules = $previewModules;
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Global Search Action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $items = [];

        if (!$this->_authorization->isAllowed('Magento_Backend::global_search')) {
            $items[] = [
                'id' => 'error',
                'type' => __('Error'),
                'name' => __('Access Denied.'),
                'description' => __('You need more permissions to do this.'),
            ];
        } else {
            $previewItems = $this->getPreviewItems();
            $searchItems = $this->getSearchItems();
            $items = array_merge_recursive($items, $previewItems, $searchItems);

            if (empty($items)) {
                $items[] = [
                    'id' => 'error',
                    'type' => __('Error'),
                    'name' => __('No search modules were registered'),
                    'description' => __(
                        'Please make sure that all global admin search modules are installed and activated.'
                    ),
                ];
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($items);
    }

    /**
     * Retrieve links to certain entities in the global search
     *
     * @return array
     */
    private function getPreviewItems()
    {
        $result = [];
        $query = $this->getRequest()->getParam('query', '');
        foreach ($this->previewModules as $previewConfig) {
            if ($previewConfig['acl'] && !$this->_authorization->isAllowed($previewConfig['acl'])) {
                continue;
            }
            if (!isset($previewConfig['url']) || !isset($previewConfig['text'])) {
                continue;
            }
            $result[] = [
                'url' => $this->getUrl($previewConfig['url']).'?search='.$query,
                'name' => __($previewConfig['text'], $query)
            ];
        }
        return $result;
    }

    /**
     * Retrieve all entity items that should appear in global search
     *
     * @return array
     */
    private function getSearchItems()
    {
        $items = [];
        $start = $this->getRequest()->getParam('start', 1);
        $limit = $this->getRequest()->getParam('limit', 10);
        $query = $this->getRequest()->getParam('query', '');
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->criteriaFactory->create();
        $searchCriteria->setLimit($limit);
        $searchCriteria->setStart($start);
        $searchCriteria->setQuery($query);
        foreach ($this->_searchModules as $searchConfig) {
            if ($searchConfig['acl'] && !$this->_authorization->isAllowed($searchConfig['acl'])) {
                continue;
            }

            $className = $searchConfig['class'];
            if (empty($className)) {
                continue;
            }
            $searchInstance = $this->itemFactory->create($className);
            $results = $searchInstance->getResults($searchCriteria);
            $items = array_merge_recursive($items, $results);
        }
        return $items;
    }
}
