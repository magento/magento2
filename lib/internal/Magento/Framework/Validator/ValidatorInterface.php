<?php
/**
 * Validator interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

/**
 * @api
 * @since 2.0.0
 */
interface ValidatorInterface extends \Zend_Validate_Interface
{
    /**
     * Set translator instance.
     *
     * @param \Magento\Framework\Translate\AdapterInterface|null $translator
     * @return \Magento\Framework\Validator\ValidatorInterface
     * @since 2.0.0
     */
    public function setTranslator($translator = null);

    /**
     * Get translator.
     *
     * @return \Magento\Framework\Translate\AdapterInterface|null
     * @since 2.0.0
     */
    public function getTranslator();

    /**
     * Check that translator is set.
     *
     * @return boolean
     * @since 2.0.0
     */
    public function hasTranslator();
}
