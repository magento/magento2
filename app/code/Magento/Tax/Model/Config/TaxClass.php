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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\Config;

/**
 * TaxClass Config
 */
class TaxClass extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Core\Model\Resource\Config
     */
    protected $resourceConfig;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Core\Model\Resource\Config $resourceConfig
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Core\Model\Resource\Config $resourceConfig,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Update the default product tax class
     *
     * @return \Magento\Tax\Model\Config\TaxClass
     */
    protected function _afterSave()
    {
        $attributeCode = "tax_class_id";

        $attribute = $this->attributeFactory->create();
        $attribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        if (!$attribute->getId()) {
            throw new \Magento\Framework\Model\Exception(__('Invalid attribute %1', $attributeCode));
        }
        $attribute->setData("default_value", $this->getData('value'));
        $attribute->save();

        return parent::_afterSave($this);
    }
}
