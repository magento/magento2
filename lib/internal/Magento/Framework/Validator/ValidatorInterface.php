<?php
/**
 * Validator interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use Laminas\Validator\ValidatorInterface as LaminasValidatorInterface;

/**
 * @api
 * @since 100.0.2
 */
interface ValidatorInterface extends LaminasValidatorInterface
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
