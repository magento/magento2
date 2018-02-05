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
    /**
     * @var SearchEntityFactory
     */
    private $searchEntityFactory;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::system/search.phtml';

    /**
     * @var array
     */
    private $entityResources;

    /**
     * @var array
     */
    private $entityPaths;

    /**
     * @param Template\Context $context
     * @param array $data
     * @param array $entityResources
     * @param array $entityPaths
     * @param SearchEntityFactory|null $searchEntityFactory
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        array $entityResources = [],
        array $entityPaths = [],
        SearchEntityFactory $searchEntityFactory = null
    ) {
        $this->entityResources = $entityResources;
        $this->entityPaths = $entityPaths;
        $this->searchEntityFactory = $searchEntityFactory ?: ObjectManager::getInstance()
            ->get(SearchEntityFactory::class);

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

        foreach ($this->entityResources as $entityType => $resource) {
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
        $urlPath = $this->entityPaths[$entityType] ?? '';

        return $this->getUrl($urlPath);
    }
}
