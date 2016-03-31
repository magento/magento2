<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wonderland\Model\Data;

/**
 * Customer attribute metadata class.
 */
class FakeAttributeMetadata extends \Magento\Framework\Api\AbstractSimpleObject implements
    \Magento\Wonderland\Api\Data\FakeAttributeMetadataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAttributeCode()
    {
        return $this->_get(self::ATTRIBUTE_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreLabel()
    {
        return $this->_get(self::STORE_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendLabel()
    {
        return $this->_get(self::FRONTEND_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getNote()
    {
        return $this->_get(self::NOTE);
    }

    /**
     * Set attribute code
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->setData(self::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * Set label of the store.
     *
     * @param string $storeLabel
     * @return $this
     */
    public function setStoreLabel($storeLabel)
    {
        return $this->setData(self::STORE_LABEL, $storeLabel);
    }

    /**
     * Set label which supposed to be displayed on frontend.
     *
     * @param string $frontendLabel
     * @return $this
     */
    public function setFrontendLabel($frontendLabel)
    {
        return $this->setData(self::FRONTEND_LABEL, $frontendLabel);
    }

    /**
     * Set the note attribute for the element.
     *
     * @param string $note
     * @return $this
     */
    public function setNote($note)
    {
        return $this->setData(self::NOTE, $note);
    }
}
