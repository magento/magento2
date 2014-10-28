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

use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;
use Magento\Framework\Service\Data\AttributeValueBuilder;

/**
 * @codeCoverageIgnore
 */
class CategoryBuilder extends AbstractExtensibleObjectBuilder
{
    /**
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param \Magento\Catalog\Service\V1\Category\MetadataServiceInterface $metadataService
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        \Magento\Catalog\Service\V1\Category\MetadataServiceInterface $metadataService
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
    }

    /**
     * Set category id
     *
     * @param  int $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->_set(Category::ID, $value);
    }

    /**
     * Set category parent id
     *
     * @param  int $value
     * @return $this
     */
    public function setParentId($value)
    {
        return $this->_set(Category::PARENT_ID, $value);
    }

    /**
     * Set path of the category
     *
     * @param  string $value
     * @return $this
     */
    public function setPath($value)
    {
        return $this->_set(Category::PATH, $value);
    }

    /**
     * Set position of the category
     *
     * @param  int $value
     * @return $this
     */
    public function setPosition($value)
    {
        return $this->_set(Category::POSITION, $value);
    }

    /**
     * Set category level
     *
     * @param  int $value
     * @return $this
     */
    public function setLevel($value)
    {
        return $this->_set(Category::LEVEL, $value);
    }

    /**
     * Set category children count
     *
     * @param  int $value
     * @return $this
     */
    public function setChildrenCount($value)
    {
        return $this->_set(Category::CHILDREN_COUNT, $value);
    }

    /**
     * Name of the created category
     *
     * @param  string $value
     * @return $this
     */
    public function setName($value)
    {
        return $this->_set(Category::NAME, $value);
    }

    /**
     * Set whether the category will be visible in the frontend
     *
     * @param  bool $value
     * @return $this
     */
    public function setActive($value)
    {
        return $this->_set(Category::ACTIVE, $value);
    }
}
