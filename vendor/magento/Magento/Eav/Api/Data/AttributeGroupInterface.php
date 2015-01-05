<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Eav\Api\Data;

interface AttributeGroupInterface
{
    const GROUP_ID = 'attribute_group_id';

    const GROUP_NAME = 'attribute_group_name';

    const ATTRIBUTE_SET_ID = 'attribute_set_id';

    /**
     * Retrieve id
     *
     * @return string|null
     */
    public function getAttributeGroupId();

    /**
     * Retrieve name
     *
     * @return string|null
     */
    public function getAttributeGroupName();

    /**
     * Retrieve attribute set id
     *
     * @return int|null
     */
    public function getAttributeSetId();
}
