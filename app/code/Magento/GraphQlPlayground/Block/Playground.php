<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlPlayground\Block;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\Store;

/**
 * Class Playground
 *
 * @package Magento\GraphQlPlayground\Block
 */
class Playground extends Template
{
    /**
     * @var \Magento\Framework\App\AreaList
     */
    private $areaList;

    /**
     * @var string
     */
    private $graphqlEndpoint;

    /**
     * Playground constructor.
     *
     * @param \Magento\Framework\App\AreaList $areaList
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        AreaList $areaList,
        Template\Context $context,
        array $data = []
    ) {
        $this->areaList = $areaList;
        parent::__construct($context, $data);
    }

    /**
     * Get Graphql Endpoint
     *
     * @return string
     */
    public function getGraphqlEndpoint(): string
    {
        if (!$this->graphqlEndpoint || strlen($this->graphqlEndpoint) == 0) {
            $this->graphqlEndpoint =
                $this->getBaseUrlFromConfig() .
                $this->areaList->getFrontName(Area::AREA_GRAPHQL);
        }
        return $this->graphqlEndpoint;
    }

    /**
     * Get Base Url from scope config to avoid store code added at the end
     *
     * @return string
     */
    private function getBaseUrlFromConfig(): string
    {
        $url = $this->_scopeConfig->getValue(Store::XML_PATH_UNSECURE_BASE_URL);
        if ($this->getRequest()->isSecure()) {
            $url = $this->_scopeConfig->getValue(Store::XML_PATH_SECURE_BASE_URL);
        }
        return $url ?: '';
    }
}
