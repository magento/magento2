<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

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
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $hasDataChanges = $this->isModified($object);
        $object->setIsOptionsSaved(false);

        $result = parent::save($object);

        if ($hasDataChanges && !$object->isOptionsSaved()) {
            $object->saveItemOptions();
        }
        return $result;
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
