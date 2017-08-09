<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

/**
 * Bundle Option Model
 *
 * @api
 * @method int getParentId()
 * @method null|\Magento\Catalog\Model\Product[] getSelections()
 * @method Option setParentId(int $value)
 */
class Option extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Bundle\Api\Data\OptionInterface
{
    /**#@+
     * Constants
     */
    const KEY_OPTION_ID = 'option_id';
    const KEY_TITLE = 'title';
    const KEY_REQUIRED = 'required';
    const KEY_TYPE = 'type';
    const KEY_POSITION = 'position';
    const KEY_SKU = 'sku';
    const KEY_PRODUCT_LINKS = 'product_links';
    /**#@-*/

    /**#@-*/
    protected $defaultSelection = null;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Bundle\Model\ResourceModel\Option::class);
        parent::_construct();
    }

    /**
     * Add selection to option
     *
     * @param \Magento\Catalog\Model\Product $selection
     * @return void
     */
    public function addSelection(\Magento\Catalog\Model\Product $selection)
    {
        if (!$this->hasData('selections')) {
            $this->setData('selections', []);
        }
        $selections = $this->getData('selections');
        $selections[] = $selection;
        $this->setSelections($selections);
    }

    /**
     * Check Is Saleable Option
     *
     * @return bool
     */
    public function isSaleable()
    {
        $saleable = false;
        $selections = $this->getSelections();
        if ($selections) {
            foreach ($selections as $selection) {
                if ($selection->isSaleable()) {
                    $saleable = true;
                    break;
                }
            }
        }
        return $saleable;
    }

    /**
     * Retrieve default Selection object
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getDefaultSelection()
    {
        if (!$this->defaultSelection && $this->getSelections()) {
            foreach ($this->getSelections() as $selection) {
                if ($selection->getIsDefault()) {
                    $this->defaultSelection = $selection;
                    break;
                }
            }
        }
        return $this->defaultSelection;
    }

    /**
     * Check is multi Option selection
     *
     * @return bool
     */
    public function isMultiSelection()
    {
        return $this->getType() == 'checkbox' || $this->getType() == 'multi';
    }

    /**
     * Retrieve options searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        return $this->_getResource()->getSearchableData($productId, $storeId);
    }

    /**
     * Return selection by it's id
     *
     * @param int $selectionId
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getSelectionById($selectionId)
    {
        $foundSelection = null;
        foreach ($this->getSelections() as $selection) {
            if ($selection->getSelectionId() == $selectionId) {
                $foundSelection = $selection;
                break;
            }
        }
        return $foundSelection;
    }

    //@codeCoverageIgnoreStart

    /**
     * {@inheritdoc}
     */
    public function getOptionId()
    {
        return $this->getData(self::KEY_OPTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(self::KEY_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequired()
    {
        return $this->getData(self::KEY_REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->getData(self::KEY_POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData(self::KEY_SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductLinks()
    {
        return $this->getData(self::KEY_PRODUCT_LINKS);
    }

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId)
    {
        return $this->setData(self::KEY_OPTION_ID, $optionId);
    }

    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::KEY_TITLE, $title);
    }

    /**
     * Set whether option is required
     *
     * @param bool $required
     * @return $this
     */
    public function setRequired($required)
    {
        return $this->setData(self::KEY_REQUIRED, $required);
    }

    /**
     * Set input type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * Set option position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        return $this->setData(self::KEY_POSITION, $position);
    }

    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(self::KEY_SKU, $sku);
    }

    /**
     * Set product links
     *
     * @param \Magento\Bundle\Api\Data\LinkInterface[] $productLinks
     * @return $this
     */
    public function setProductLinks(array $productLinks = null)
    {
        return $this->setData(self::KEY_PRODUCT_LINKS, $productLinks);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Bundle\Api\Data\OptionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Bundle\Api\Data\OptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Bundle\Api\Data\OptionExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
