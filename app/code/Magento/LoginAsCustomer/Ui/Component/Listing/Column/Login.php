<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;

class Login extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $_authorization;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization,
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->_authorization = $authorization;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $hidden = !$this->_authorization->isAllowed('Magento_LoginAsCustomer::login_button');
            foreach ($dataSource['data']['items'] as &$item) {

                $sourceColumnName = !empty($item['customer_id']) ? 'customer_id' : 'entity_id';

                if (!empty($item[$sourceColumnName])) {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'loginascustomer/login/login',
                            ['customer_id' => $item[$sourceColumnName]]
                        ),
                        'label' => __('Login As Customer'),
                        'hidden' => $hidden,
                        'target' => '_blank',
                    ];
                }
            }
        }

        return $dataSource;
    }
}
