<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Adminhtml search synonyms controller
 *
 */
abstract class Synonyms extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory
     */
    protected $forwardFactory;

    /**
     * @var \Magento\Framework\Registry $registry
     */
    protected $registry;

    /**
     * @var \Magento\Search\Model\EngineResolver $engineResolver
     */
    protected $engineResolver;

    /**
     * @var \Magento\Framework\Search\SearchEngine\ConfigInterface $searchFeatureConfig
     */
    protected $searchFeatureConfig;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Search\Model\EngineResolver $engineResolver
     * @param \Magento\Framework\Search\SearchEngine\ConfigInterface $searchFeatureConfig
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Search\Model\EngineResolver $engineResolver,
        \Magento\Framework\Search\SearchEngine\ConfigInterface $searchFeatureConfig
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->forwardFactory = $forwardFactory;
        $this->registry = $registry;
        $this->engineResolver = $engineResolver;
        $this->searchFeatureConfig = $searchFeatureConfig;
        parent::__construct($context);
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $this->checkSearchEngineSupport();
        /** @var \Magento\Backend\Model\View\Result\Page  $resultPage **/
        $resultPage = $this->resultPageFactory->create();

        // Make it active on menu and set breadcrumb trail
        $resultPage->setActiveMenu('Magento_Search::search_synonyms');
        $resultPage->addBreadcrumb(__('Marketing'), __('Marketing'));
        $resultPage->addBreadcrumb(__('Search Synonyms'), __('Search Synonyms'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Search::synonyms');
    }

    /**
     * Checks if 'synonyms' feature is supported by configured search engine. If not supported displays a notice
     *
     * @return void
     */
    protected function checkSearchEngineSupport()
    {
        // Display a notice if search engine configuration does not support synonyms
        $searchEngine = $this->engineResolver->getCurrentSearchEngine();
        if (!$this->searchFeatureConfig
            ->isFeatureSupported(
                \Magento\Framework\Search\SearchEngine\ConfigInterface::SEARCH_ENGINE_FEATURE_SYNONYMS,
                $searchEngine
            )
        ) {
            // Display anotice indicating search synonyms feature is not supported for the selected search engine
            $this->messageManager
                ->addNoticeMessage(
                    __(
                        'Search synonyms are not supported by the %1 search engine. '
                        . 'Any synonyms you enter won\'t be used.',
                        $searchEngine
                    )
                );
        }
    }
}
