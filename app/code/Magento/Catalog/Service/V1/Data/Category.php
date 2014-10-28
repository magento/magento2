<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Data;

use \Magento\Framework\Service\Data\AbstractExtensibleObject;

/**
 * @codeCoverageIgnore
 */
class Category extends AbstractExtensibleObject
{
    const ID = 'id';

    const PARENT_ID = 'parent_id';

    const PATH = 'path';

    const POSITION = 'position';

    const LEVEL = 'level';

    const CHILDREN_COUNT = 'children_count';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const NAME = 'name';

    const ACTIVE = 'active';

    /**
     * Category id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Category parent id
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->_get(self::PARENT_ID);
    }

    /**
     * Path of the category
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->_get(self::PATH);
    }

    /**
     * Position of the category
     *
     * @return int|null
     */
    public function getPosition()
    {
        return $this->_get(self::POSITION);
    }

    /**
     * Category level
     *
     * @return int|null
     */
    public function getLevel()
    {
        return $this->_get(self::LEVEL);
    }

    /**
     * Category children count
     *
     * @return int|null
     */
    public function getChildrenCount()
    {
        return $this->_get(self::CHILDREN_COUNT);
    }

    /**
     * Category created date
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Category updated date
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Name of the created category
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Defines whether the category will be visible in the frontend
     *
     * @return bool|null
     */
    public function isActive()
    {
        return (bool)$this->_get(self::ACTIVE);
    }
}
