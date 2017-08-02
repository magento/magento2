<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Ui\Component;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

/**
 * Class \Magento\Cms\Ui\Component\DataProvider
 *
 * @since 2.1.0
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var AuthorizationInterface
     * @since 2.2.0
     */
    private $authorization;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );

        $this->meta = array_replace_recursive($meta, $this->prepareMetadata());
    }

    /**
     * @deprecated 2.2.0
     * @return AuthorizationInterface|mixed
     * @since 2.2.0
     */
    private function getAuthorizationInstance()
    {
        if ($this->authorization === null) {
            $this->authorization = ObjectManager::getInstance()->get(AuthorizationInterface::class);
        }
        return $this->authorization;
    }

    /**
     * Prepares Meta
     *
     * @return array
     * @since 2.2.0
     */
    public function prepareMetadata()
    {
        $metadata = [];

        if (!$this->getAuthorizationInstance()->isAllowed('Magento_Cms::save')) {
            $metadata = [
                'cms_page_columns' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'editorConfig' => [
                                    'enabled' => false
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $metadata;
    }
}
