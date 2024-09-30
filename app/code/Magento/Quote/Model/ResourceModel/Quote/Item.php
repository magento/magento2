<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Quote\Model\Quote\Item\Option;

/**
 * Quote resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Item extends AbstractDb
{
    /**
     * Main table and field initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote_item', 'item_id');
    }

    /**
     * {@inheritdoc}
     */
    public function save(AbstractModel $object)
    {
        $hasDataChanges = $this->isModified($object);
        $object->setIsOptionsSaved(false);

        $result = parent::save($object);

        if (!$object->isOptionsSaved() && ($hasDataChanges || $this->hasOptionsChanged($object))) {
            $object->saveItemOptions();
        }
        return $result;
    }

    /**
     * Check if quote item options have changed.
     *
     * @param AbstractModel $object
     * @return bool
     */
    private function hasOptionsChanged(AbstractModel $object): bool
    {
        $hasDataChanges = false;
        $options = $object->getOptions() ?? [];
        foreach ($options as $option) {
            /** @var Option $option */
            if (!$option->getId() || $option->getResource()->hasDataChanged($option)) {
                $hasDataChanges = true;
                break;
            }
        }
        return $hasDataChanges;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareDataForUpdate($object)
    {
        $data = parent::prepareDataForUpdate($object);

        if (isset($data['updated_at'])) {
            unset($data['updated_at']);
        }

        return $data;
    }
}
