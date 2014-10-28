<?php
/**
 * Builder for media attribute
 *
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
namespace Magento\Catalog\Service\V1\Product\Attribute\Media\Data;

use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;

/**
 * @codeCoverageIgnore
 */
class GalleryEntryBuilder extends AbstractExtensibleObjectBuilder
{
    /**
     * Set gallery entity ID
     *
     * @param int $entityId
     * @return $this
     */
    public function setId($entityId)
    {
        return $this->_set(GalleryEntry::ID, $entityId);
    }

    /**
     * Set media alternative text
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->_set(GalleryEntry::LABEL, $label);
    }

    /**
     * Set gallery entity position (sort order)
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        return $this->_set(GalleryEntry::POSITION, $position);
    }

    /**
     * Set disabled flag that shows if gallery entity is hidden from product page
     *
     * @param bool $isDisabled
     * @return $this
     */
    public function setDisabled($isDisabled)
    {
        return $this->_set(GalleryEntry::DISABLED, $isDisabled);
    }

    /**
     * Set gallery entry types (thumbnail, image, small_image etc)
     *
     * @param array $roles
     * @return $this
     */
    public function setTypes(array $roles)
    {
        return $this->_set(GalleryEntry::TYPES, $roles);
    }

    /**
     * Set file path
     *
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        return $this->_set(GalleryEntry::FILE, $file);
    }
}
