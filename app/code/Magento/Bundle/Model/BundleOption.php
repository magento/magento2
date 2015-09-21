<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Bundle\Api\Data\BundleOptionInterface;

class BundleOption extends AbstractExtensibleModel implements BundleOptionInterface
{
    /**#@+
     * Constants
     */
    const OPTION_ID = 'option_id';
    const OPTION_QTY = 'option_qty';
    const OPTION_SELECTIONS = 'option_selections';
    /**#@-*/

    //@codeCoverageIgnoreStart
    /**
     * {@inheritdoc}
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionQty()
    {
        return $this->getData(self::OPTION_QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionSelections()
    {
        return $this->getData(self::OPTION_SELECTIONS);
    }

    /**
     * Set option id.
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId)
    {
        return $this->setData(self::OPTION_ID, $optionId);
    }

    /**
     * Set option quantity.
     *
     * @param int $optionQty
     * @return $this
     */
    public function setOptionQty($optionQty)
    {
        return $this->setData(self::OPTION_QTY, $optionQty);
    }

    /**
     * Set option selections.
     *
     * @param int[] $optionSelections
     * @return $this
     */
    public function setOptionSelections(array $optionSelections)
    {
        return $this->setData(self::OPTION_SELECTIONS, $optionSelections);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Bundle\Api\Data\BundleOptionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Bundle\Api\Data\BundleOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Bundle\Api\Data\BundleOptionExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    //@codeCoverageIgnoreEnd
}
