<?php
namespace Smetana\Third\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Smetana\Third\Api\Data\PartnerInterface;

/**
 * Action buttons of Partner grid class
 *
 * @package Smetana\Third\Ui\Component\Listing\Column
 */
class PartnerActions extends Column
{
    /**
     * Path to edit Partner
     *
     * @var String
     */
    const PATH_TO_EDIT = 'smetana_third_admin/partner/edit';

    /**
     * Path to delete Partner
     *
     * @var String
     */
    const PATH_TO_DELETE = 'smetana_third_admin/partner/delete';

    /**
     * Url instance
     *
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[PartnerInterface::PARTNER_ID])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::PATH_TO_EDIT,
                                [
                                    'id' => $item[PartnerInterface::PARTNER_ID]
                                ]
                            ),
                            'label' => __('Edit'),
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::PATH_TO_DELETE,
                                [
                                    'id' => $item[PartnerInterface::PARTNER_ID]
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "${ $.$data.partner_name }"'),
                                'message' => __('Are you sure you wan\'t to delete a "${ $.$data.partner_name }" record?'),
                            ],
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
