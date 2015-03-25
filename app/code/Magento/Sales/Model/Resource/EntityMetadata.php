<?php
/**
 * Created by PhpStorm.
 * User: akaplya
 * Date: 25.03.15
 * Time: 15:01
 */

namespace Magento\Sales\Model\Resource;

use Magento\Sales\Model\AbstractModel;

/**
 * Class EntityMetadata
 */
class EntityMetadata
{
    /**
     * @var array
     */
    protected $metadataInfo = [];

    /**
     * @param AbstractModel $entity
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFields(AbstractModel $entity)
    {
        if (!isset($this->metadataInfo[get_class($entity)])) {
            $this->metadataInfo[get_class($entity)] =
                $entity->getResource()->getReadConnection()->describeTable(
                    $entity->getResource()->getMainTable()
                );
        }
        return $this->metadataInfo[get_class($entity)];
    }
}
