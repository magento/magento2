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

namespace Magento\Framework\Service\Data;

use Magento\Framework\Api\Data\ExtensibleDataBuilderInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\ObjectManager;

/**
 * Implementation for \Magento\Framework\Api\Data\ExtensibleDataBuilderInterface.
 */
class ExtensibleDataBuilder implements ExtensibleDataBuilderInterface
{
    /**
     * @var string
     */
    protected $modelClassInterface;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Initialize the builder
     *
     * @param ObjectManager $objectManager
     * @param string $modelClassInterface
     */
    public function __construct(ObjectManager $objectManager, $modelClassInterface)
    {
        $this->objectManager = $objectManager;
        $this->modelClassInterface = $modelClassInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomAttribute(\Magento\Framework\Api\Data\AttributeInterface $attribute)
    {
        // Store as an associative array for easier lookup and processing
        $this->data[AbstractExtensibleModel::CUSTOM_ATTRIBUTES_KEY][$attribute->getAttributeCode()]
            = $attribute;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomAttributes(array $attributes)
    {
        foreach ($attributes as $attribute) {
            $this->setCustomAttribute($attribute);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->objectManager->create(
            $this->modelClassInterface,
            ['data' => $this->data]
        );
    }
}
