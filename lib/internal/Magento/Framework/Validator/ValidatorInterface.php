<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use Laminas\Validator\Translator\TranslatorInterface;
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
     * @param TranslatorInterface|null $translator
     * @return ValidatorInterface
     */
    public function setTranslator(?TranslatorInterface $translator = null);

    /**
     * Get translator.
     *
     * @return TranslatorInterface|null
     */
    public function getTranslator();

    /**
     * Check that translator is set.
     *
     * @return boolean
     */
    public function hasTranslator();
}
