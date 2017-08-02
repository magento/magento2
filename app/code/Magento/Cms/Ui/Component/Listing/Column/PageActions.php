<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Ui\Component\Listing\Column;

use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class PageActions
 * @since 2.0.0
 */
class PageActions extends Column
{
    /** Url path */
    const CMS_URL_PATH_EDIT = 'cms/page/edit';
    const CMS_URL_PATH_DELETE = 'cms/page/delete';

    /**
     * @var \Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder
     * @since 2.0.0
     */
    protected $actionUrlBuilder;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * @var string
     * @since 2.0.0
     */
    private $editUrl;

    /**
     * @var Escaper
     * @since 2.2.0
     */
    private $escaper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     * @since 2.0.0
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::CMS_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @since 2.0.0
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['page_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['page_id' => $item['page_id']]),
                        'label' => __('Edit')
                    ];
                    $title = $this->getEscaper()->escapeHtml($item['title']);
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::CMS_URL_PATH_DELETE, ['page_id' => $item['page_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete %1', $title),
                            'message' => __('Are you sure you want to delete a %1 record?', $title)
                        ]
                    ];
                }
                if (isset($item['identifier'])) {
                    $item[$name]['preview'] = [
                        'href' => $this->actionUrlBuilder->getUrl(
                            $item['identifier'],
                            isset($item['_first_store_id']) ? $item['_first_store_id'] : null,
                            isset($item['store_code']) ? $item['store_code'] : null
                        ),
                        'label' => __('View')
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get instance of escaper
     * @return Escaper
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = ObjectManager::getInstance()->get(Escaper::class);
        }
        return $this->escaper;
    }
}
