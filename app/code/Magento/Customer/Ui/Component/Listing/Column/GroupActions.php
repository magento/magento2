<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Escaper;

/**
 * Class GroupActions
 *
 * Customer Groups actions column
 */
class GroupActions extends Column
{
    /**
     * Url path
     */
    public const URL_PATH_EDIT = 'customer/group/edit';
    public const URL_PATH_DELETE = 'customer/group/delete';

    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     * @param GroupManagementInterface $groupManagement
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        array $components = [],
        array $data = [],
        GroupManagementInterface $groupManagement = null
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->groupManagement = $groupManagement ?: ObjectManager::getInstance()->get(GroupManagementInterface::class);

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['customer_group_id'])) {
                    $title = $this->escaper->escapeHtml($item['customer_group_code']);
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'id' => $item['customer_group_id']
                                ]
                            ),
                            'label' => __('Edit'),
                        ],
                    ];

                    if (!$this->canHideDeleteButton((int) $item['customer_group_id'])) {
                        $item[$this->getData('name')]['delete'] = [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'id' => $item['customer_group_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete %1', $this->escaper->escapeHtml($title)),
                                'message' => __(
                                    'Are you sure you want to delete a %1 record?',
                                    $this->escaper->escapeHtml($title)
                                )
                            ],
                            'post' => true,
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }

    /**
     * Check if delete button can visible
     *
     * @param int $customer_group_id
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canHideDeleteButton(int $customer_group_id): bool
    {
        return $this->groupManagement->isReadonly($customer_group_id);
    }
}
