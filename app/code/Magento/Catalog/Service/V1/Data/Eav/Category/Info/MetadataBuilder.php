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

namespace Magento\Catalog\Service\V1\Data\Eav\Category\Info;

use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;
use Magento\Framework\Service\Data\AttributeValueBuilder;

/**
 * Class MetadataBuilder
 *
 * @codeCoverageIgnore
 */
class MetadataBuilder extends AbstractExtensibleObjectBuilder
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
     * @param int $value
     * @return $this
     */
    public function setCategoryId($value)
    {
        $this->_set(Metadata::ID, $value);
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setPosition($value)
    {
        $this->_set(Metadata::POSITION, $value);
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setLevel($value)
    {
        $this->_set(Metadata::LEVEL, $value);
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setParentId($value)
    {
        $this->_set(Metadata::PARENT_ID, $value);
        return $this;
    }

    /**
     * @param int[] $value
     * @return $this
     */
    public function setChildren($value)
    {
        $this->_set(Metadata::CHILDREN, $value);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setCreatedAt($value)
    {
        $this->_set(Metadata::CREATED_AT, $value);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        $this->_set(Metadata::UPDATED_AT, $value);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setName($value)
    {
        $this->_set(Metadata::NAME, $value);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setUrlKey($value)
    {
        $this->_set(Metadata::URL_KEY, $value);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPath($value)
    {
        $this->_set(Metadata::PATH, $value);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDisplayMode($value)
    {
        $this->_set(Metadata::DISPLAY_MODE, $value);
        return $this;
    }

    /**
     * @param string[] $value
     * @return $this
     */
    public function setAvailableSortBy($value)
    {
        $this->_set(Metadata::AVAILABLE_SORT_BY, $value);
        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setIncludeInMenu($value)
    {
        $this->_set(Metadata::INCLUDE_IN_MENU, $value);
        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setActive($value)
    {
        $this->_set(Metadata::ACTIVE, $value);
        return $this;
    }
}
