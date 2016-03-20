<?php
/**
 * Validator interface
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

/**
 * @api
 */
interface ValidatorInterface extends \Zend_Validate_Interface
{
    /**
     * Set translator instance.
     *
     * @param \Magento\Framework\Translate\AdapterInterface|null $translator
     * @return \Magento\Framework\Validator\ValidatorInterface
     */
    public function setTranslator($translator = null);

    /**
     * Get translator.
     *
     * @return \Magento\Framework\Translate\AdapterInterface|null
     */
    public function getTranslator();

    /**
     * Check that translator is set.
     *
     * @return boolean
     */
    public function hasTranslator();
}
