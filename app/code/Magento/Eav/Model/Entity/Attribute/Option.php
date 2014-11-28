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
namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Emtity attribute option model
 *
 * @method \Magento\Eav\Model\Resource\Entity\Attribute\Option _getResource()
 * @method \Magento\Eav\Model\Resource\Entity\Attribute\Option getResource()
 * @method int getAttributeId()
 * @method \Magento\Eav\Model\Entity\Attribute\Option setAttributeId(int $value)
 * @method \Magento\Eav\Model\Entity\Attribute\Option setSortOrder(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Option extends AbstractExtensibleModel implements AttributeOptionInterface
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Magento\Eav\Model\Resource\Entity\Attribute\Option');
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(AttributeOptionInterface::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData(AttributeOptionInterface::VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(AttributeOptionInterface::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDefault()
    {
        return $this->getData(AttributeOptionInterface::IS_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreLabels()
    {
        return $this->getData(AttributeOptionInterface::STORE_LABELS);
    }
}
