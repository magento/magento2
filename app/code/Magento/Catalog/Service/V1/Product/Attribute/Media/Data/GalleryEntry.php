<?php
/**
 * Product Media Attribute
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

use \Magento\Framework\Service\Data\AbstractExtensibleObject;

/**
 * @codeCoverageIgnore
 */
class GalleryEntry extends AbstractExtensibleObject
{
    const ID = 'id';
    const LABEL = 'label';
    const POSITION = 'position';
    const DISABLED = 'disabled';
    const TYPES = 'types';
    const FILE = 'file';

    /**
     * Retrieve gallery entry ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Retrieve gallery entry alternative text
     *
     * @return string|null
     */
    public function getLabel()
    {
        return $this->_get(self::LABEL);
    }

    /**
     * Retrieve gallery entry position (sort order)
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->_get(self::POSITION);
    }

    /**
     * Check if gallery entry is hidden from product page
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->_get(self::DISABLED);
    }

    /**
     * Retrieve gallery entry image types (thumbnail, image, small_image etc)
     *
     * @return string[]|null
     */
    public function getTypes()
    {
        return $this->_get(self::TYPES);
    }

    /**
     * Get file path
     *
     * @return string|null
     */
    public function getFile()
    {
        return $this->_get(self::FILE);
    }
}
