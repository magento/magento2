<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Index;

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
    protected $previewModules;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param array $searchModules
     * @param array $previewModules
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        array $searchModules = [],
        array $previewModules = []
    )
    {
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
     * @return array
     */
    protected function getPreviewItems()
    {
        $result = [];
        $query = $this->getRequest()->getParam('query', '');
        foreach ($this->previewModules as $previewConfig) {
            if ($previewConfig['acl'] && !$this->_authorization->isAllowed($previewConfig['acl'])) {
                continue;
            }
            if (!$previewConfig['url'] || !$previewConfig['text']) {
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
     * @return array
     */
    protected function getSearchItems()
    {
        $items = [];
        $start = $this->getRequest()->getParam('start', 1);
        $limit = $this->getRequest()->getParam('limit', 10);
        $query = $this->getRequest()->getParam('query', '');
        foreach ($this->_searchModules as $searchConfig) {
            if ($searchConfig['acl'] && !$this->_authorization->isAllowed($searchConfig['acl'])) {
                continue;
            }

            $className = $searchConfig['class'];
            if (empty($className)) {
                continue;
            }
            $searchInstance = $this->_objectManager->create($className);
            $results = $searchInstance->setStart(
                $start
            )->setLimit(
                $limit
            )->setQuery(
                $query
            )->load()->getResults();
            $items = array_merge_recursive($items, $results);
        }
        return $items;
    }
}
