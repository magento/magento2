<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Test\Fixture;

use Mtf\Fixture\DataFixture;

/**
 * ACL resources fixture
 *
 */
class Resource extends DataFixture
{
    /**
     * resources from the three in array
     * key is resource id, value is parent id
     *
     * @var array
     */
    protected $resources = [
        'Magento_Adminhtml::dashboard' => null,
        'Magento_Sales::sales' => null,
        'Magento_Sales::sales_operation' => 'Magento_Sales::sales',
        'Magento_Sales::sales_order' => 'Magento_Sales::sales_operation',
        'Magento_Sales::actions' => 'Magento_Sales::sales_order',
        'Magento_Sales::create' => 'Magento_Sales::actions',
        'Magento_Sales::actions_view' => 'Magento_Sales::actions',
        'Magento_Sales::email' => 'Magento_Sales::actions',
        'Magento_Sales::reorder' => 'Magento_Sales::actions',
        'Magento_Sales::actions_edit' => 'Magento_Sales::actions',
        'Magento_Sales::cancel' => 'Magento_Sales::actions',
        'Magento_Sales::review_payment' => 'Magento_Sales::actions',
        'Magento_Sales::capture' => 'Magento_Sales::actions',
        'Magento_Sales::invoice' => 'Magento_Sales::actions',
        'Magento_Sales::creditmemo' => 'Magento_Sales::actions',
        'Magento_Sales::hold' => 'Magento_Sales::actions',
        'Magento_Sales::unhold' => 'Magento_Sales::actions',
        'Magento_Sales::ship' => 'Magento_Sales::actions',
        'Magento_Sales::comment' => 'Magento_Sales::actions',
        'Magento_Sales::emails' => 'Magento_Sales::actions',
        'Magento_Sales::sales_invoice' => 'Magento_Sales::sales_operation',
        'Magento_Sales::shipment' => 'Magento_Sales::sales_operation',
        'Magento_Sales::sales_creditmemo' => 'Magento_Sales::sales_operation',
        'Magento_Paypal::billing_agreement' => 'Magento_Sales::sales_operation',
        'Magento_Paypal::billing_agreement_actions' => 'Magento_Paypal::billing_agreement',
        'Magento_Paypal::billing_agreement_actions_view' => 'Magento_Paypal::billing_agreement_actions',
        'Magento_Paypal::actions_manage' => 'Magento_Paypal::billing_agreement_actions',
        'Magento_Paypal::use' => 'Magento_Paypal::billing_agreement_actions',
        'Magento_Sales::transactions' => 'Magento_Sales::sales_operation',
        'Magento_Sales::transactions_fetch' => 'Magento_Sales::transactions',
    ];

    /**
     * {@inheritdoc}
     */
    protected function _initData()
    {
    }

    /**
     * Just a stub of inherited method
     *
     * @throws \BadMethodCallException
     */
    public function persist()
    {
        throw new \BadMethodCallException('This method is not applicable here. It is data provider for role fixture');
    }

    /**
     * Return requested resource, all it's children and parents
     *
     * @param string $resourceId
     * @throws \InvalidArgumentException
     * @return array
     */
    public function get($resourceId = null)
    {
        if (!array_key_exists($resourceId, $this->resources)) {
            throw new \InvalidArgumentException('No resource "' . $resourceId . '" found');
        }
        $withParents = $this->getParents($resourceId);
        $withParents[] = $resourceId;
        return array_merge($withParents, $this->getChildren($resourceId));
    }

    /**
     * Get all direct parents
     *
     * @param string $resourceId
     * @return array
     */
    protected function getParents($resourceId)
    {
        if (is_null($this->resources[$resourceId])) {
            return [];
        }

        $parents = [];
        $current = $this->resources[$resourceId];

        while (!is_null($this->resources[$current])) {
            $parents[] = $current;
            $current = $this->resources[$current];
        }
        $parents[] = $current;

        return $parents;
    }

    /**
     * Get all child resources
     *
     * @param string $resourceId
     * @return array
     */
    protected function getChildren($resourceId)
    {
        $children = array_keys($this->resources, $resourceId);
        $result = $children;
        foreach ($children as $child) {
            $result = array_merge($result, $this->getChildren($child));
        }
        return $result;
    }
}
