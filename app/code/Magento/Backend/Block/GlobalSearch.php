<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block;

use Magento\Backend\Model\GlobalSearch\SearchEntityFactory;
use Magento\Backend\Model\GlobalSearch\SearchEntity;
use Magento\Framework\App\ObjectManager;

/**
 * @api
 * @since 100.0.2
 */
class GlobalSearch extends \Magento\Backend\Block\Template
{
    const ENTITY_TYPE_PRODUCTS = 'Products';
    const ENTITY_TYPE_ORDERS = 'Orders';
    const ENTITY_TYPE_CUSTOMERS = 'Customers';
    const ENTITY_TYPE_PAGES = 'Pages';

    /**
     * Affiliation between entity types for global search and corresponding admin resources.
     *
     * @var array
     */
    private $entityTypes = [
        self::ENTITY_TYPE_PRODUCTS => \Magento\Catalog\Controller\Adminhtml\Product::ADMIN_RESOURCE,
        self::ENTITY_TYPE_ORDERS => \Magento\Sales\Controller\Adminhtml\Order::ADMIN_RESOURCE,
        self::ENTITY_TYPE_CUSTOMERS => \Magento\Customer\Controller\Adminhtml\Index::ADMIN_RESOURCE,
        self::ENTITY_TYPE_PAGES => \Magento\Cms\Controller\Adminhtml\Page\Index::ADMIN_RESOURCE,
    ];

    /**
     * @var SearchEntityFactory
     */
    private $searchEntityFactory;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::system/search.phtml';

    /**
     * @param Template\Context $context
     * @param array $data
     * @param SearchEntityFactory|null $searchEntityFactory
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        SearchEntityFactory $searchEntityFactory = null
    ) {
        $this->searchEntityFactory = $searchEntityFactory ?: ObjectManager::getInstance()->get(
            SearchEntityFactory::class
        );

        parent::__construct($context, $data);
    }

    /**
     * Get components configuration
     * @return array
     */
    public function getWidgetInitOptions()
    {
        return [
            'suggest' => [
                'dropdownWrapper' => '<div class="autocomplete-results" ></div >',
                'template' => '[data-template=search-suggest]',
                'termAjaxArgument' => 'query',
                'source' => $this->getUrl('adminhtml/index/globalSearch'),
                'filterProperty' => 'name',
                'preventClickPropagation' => false,
                'minLength' => 2,
            ]
        ];
    }

    /**
     * Get entities which are allowed to show.
     *
     * @return SearchEntity[]
     */
    public function getEntitiesToShow()
    {
        $allowedEntityTypes = [];
        $entitiesToShow = [];

        foreach ($this->entityTypes as $entityType => $resource) {
            if ($this->getAuthorization()->isAllowed($resource)) {
                $allowedEntityTypes[] = $entityType;
            }
        }

        foreach ($allowedEntityTypes as $entityType) {
            $url = $this->getUrlEntityType($entityType);

            $searchEntity = $this->searchEntityFactory->create();
            $searchEntity->setId('searchPreview' . $entityType);
            $searchEntity->setTitle('in ' . $entityType);
            $searchEntity->setUrl($url);

            $entitiesToShow[] = $searchEntity;
        }

        return $entitiesToShow;
    }

    /**
     * Get url path by entity type.
     *
     * @param string $entityType
     *
     * @return string
     */
    private function getUrlEntityType(string $entityType)
    {
        $urlPath = '';

        switch ($entityType) {
            case self::ENTITY_TYPE_PRODUCTS:
                $urlPath = 'catalog/product/index/';
                break;
            case self::ENTITY_TYPE_ORDERS:
                $urlPath = 'sales/order/index/';
                break;
            case self::ENTITY_TYPE_CUSTOMERS:
                $urlPath = 'customer/index/index/';
                break;
            case self::ENTITY_TYPE_PAGES:
                $urlPath = 'cms/page/index/';
                break;
        }

        return $this->getUrl($urlPath);
    }
}
