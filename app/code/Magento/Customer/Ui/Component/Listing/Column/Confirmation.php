<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class \Magento\Customer\Ui\Component\Listing\Column\Confirmation
 *
 * @since 2.1.0
 */
class Confirmation extends Column
{
    /**
     * @var ScopeConfigInterface
     * @since 2.1.0
     */
    private $scopeConfig;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param array $components
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ScopeConfigInterface $scopeConfig,
        array $components,
        array $data
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->getFieldLabel($item);
            }
        }
        return $dataSource;
    }

    /**
     * Retrieve field label
     *
     * @param array $item
     * @return string
     * @since 2.1.0
     */
    private function getFieldLabel(array $item)
    {
        if ($this->isConfirmationRequired($item)) {
            if ($item[$this->getData('name')] === null) {
                return __('Confirmed');
            }
            return __('Confirmation Required');
        }
        return __('Confirmation Not Required');
    }

    /**
     * Check if confirmation is required
     *
     * @param array $item
     * @return bool
     * @since 2.1.0
     */
    private function isConfirmationRequired(array $item)
    {
        return (bool)$this->scopeConfig->getValue(
            AccountManagement::XML_PATH_IS_CONFIRM,
            ScopeInterface::SCOPE_WEBSITES,
            $item['website_id'][0]
        );
    }
}
