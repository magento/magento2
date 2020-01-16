<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Form\Element\Newsletter;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

/**
 * Customer Newsletter Subscriptions Element
 */
class Subscriptions extends AbstractElement
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param DataPersistorInterface $dataPersistor
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        DataPersistorInterface $dataPersistor,
        $data = []
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @inheritdoc
     */
    public function getElementHtml()
    {
        $websiteHeader = $this->_escape(__('Website'));
        $subscribedHeader = $this->_escape(__('Subscribed'));
        $storeHeader = $this->_escape(__('Store View'));
        $lastUpdatedHeader = $this->_escape(__('Last Updated At'));
        $bodyHtml = '';
        foreach ($this->getData('subscriptions') as $subscriptions) {
            $storeId = !empty($subscriptions['store_id']) ? (int)$subscriptions['store_id'] : null;
            $websiteId = (int)$subscriptions['website_id'];
            $websiteName = $this->_escape($subscriptions['website_name']);
            $subscribed = (bool)$subscriptions['status'];
            $options = (array)$subscriptions['store_options'];
            $statusElement = $this->getSubscriptionStatusElementHtml($websiteId, $subscribed);
            $storeSelectElement = $this->getStoreSelectElementHtml($websiteId, $options, $storeId);
            $lastUpdated = !empty($subscriptions['last_updated']) ? $subscriptions['last_updated'] : '';

            $bodyHtml .= "<tr><td>{$websiteName}</td><td class=\"subscriber-status\">$statusElement</td>"
                . "<td>$storeSelectElement</td><td>$lastUpdated</td></tr>";
        }
        $html = '<table class="admin__table-secondary">'
            . "<tr><th>{$websiteHeader}</th><th class=\"subscriber-status\">{$subscribedHeader}</th>"
            . "<th>{$storeHeader}</th><th>{$lastUpdatedHeader}</th></tr>"
            . $bodyHtml
            . '</table>';

        return $html;
    }

    /**
     * Get store select element html
     *
     * @param int $websiteId
     * @param array $options
     * @param int|null $value
     * @return string
     */
    private function getStoreSelectElementHtml(int $websiteId, array $options, ?int $value): string
    {
        $name = $this->getData('name');
        $value = $this->getSessionFormValue("{$name}_store", $websiteId) ?? $value;
        $elementId = $name . '_store_' . $websiteId;
        $element = $this->_factoryElement->create(
            'select',
            [
                'data' => [
                    'name' => "{$name}_store[$websiteId]",
                    'data-form-part' => $this->getData('target_form'),
                    'values' => $options,
                    'value' => $value,
                    'required' => true,
                ],
            ]
        );
        $element->setId($elementId);
        $element->setForm($this->getForm());
        if ($this->getReadonly()) {
            $element->setReadonly($this->getReadonly(), $this->getDisabled());
        }

        return $element->toHtml();
    }

    /**
     * Get subscription status element html
     *
     * @param int $websiteId
     * @param bool $value
     * @return string
     */
    private function getSubscriptionStatusElementHtml(int $websiteId, bool $value): string
    {
        $name = $this->getData('name');
        $value = $this->getSessionFormValue("{$name}_status", $websiteId) ?? $value;
        $elementId = $name . '_status_' . $websiteId;
        $element = $this->_factoryElement->create(
            'checkbox',
            [
                'data' => [
                    'name' => "{$name}_status[$websiteId]",
                    'data-form-part' => $this->getData('target_form'),
                    'value' => $value,
                    'onchange' => 'this.value = this.checked;',
                ],
            ]
        );
        $element->setId($elementId);
        $element->setForm($this->getForm());
        $element->setIsChecked($value);
        if ($this->getReadonly()) {
            $element->setReadonly($this->getReadonly(), $this->getDisabled());
        }

        return $element->toHtml();
    }

    /**
     * Get form data value from current session
     *
     * @param string $name
     * @param int $arrayKey
     * @return string|null
     */
    private function getSessionFormValue(string $name, int $arrayKey): ?string
    {
        $data = $this->dataPersistor->get('customer_form');
        $currentCustomerId = $this->getData('customer_id');
        $sessionCustomerId = $data['customer']['entity_id'] ?? null;
        if ($sessionCustomerId === null || $currentCustomerId !== (int)$sessionCustomerId) {
            return null;
        }

        return $data[$name][$arrayKey] ?? null;
    }
}
