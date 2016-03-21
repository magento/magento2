<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wonderland\Api\Data;

/**
 * Customer attribute metadata interface.
 */
interface FakeAttributeMetadataInterface extends \Magento\Framework\Api\MetadataObjectInterface
{
    /**#@+
     * Constants used as keys of data array
     */
    const ATTRIBUTE_CODE = 'attribute_code';
    const STORE_LABEL = 'store_label';
    const FRONTEND_LABEL = 'frontend_label';
    const NOTE = 'note';
    /**#@-*/

    /**
     * Get label of the store.
     *
     * @return string
     */
    public function getStoreLabel();

    /**
     * Set label of the store.
     *
     * @param string $storeLabel
     * @return $this
     */
    public function setStoreLabel($storeLabel);

    /**
     * Get label which supposed to be displayed on frontend.
     *
     * @return string
     */
    public function getFrontendLabel();

    /**
     * Set label which supposed to be displayed on frontend.
     *
     * @param string $frontendLabel
     * @return $this
     */
    public function setFrontendLabel($frontendLabel);

    /**
     * Get the note attribute for the element.
     *
     * @return string
     */
    public function getNote();

    /**
     * Set the note attribute for the element.
     *
     * @param string $note
     * @return $this
     */
    public function setNote($note);
}
