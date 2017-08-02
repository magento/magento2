<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

/**
 * Newsletter session model
 * @since 2.0.0
 */
class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * Set error message
     *
     * @param string $message
     * @return $this
     * @since 2.0.0
     */
    public function addError($message)
    {
        $this->setErrorMessage($message);
        return $this;
    }

    /**
     * Set success message
     *
     * @param string $message
     * @return $this
     * @since 2.0.0
     */
    public function addSuccess($message)
    {
        $this->setSuccessMessage($message);
        return $this;
    }

    /**
     * Get error message
     *
     * @return string $message
     * @since 2.0.0
     */
    public function getError()
    {
        $message = $this->getErrorMessage();
        $this->unsErrorMessage();
        return $message;
    }

    /**
     * Get success message
     *
     * @return string $message
     * @since 2.0.0
     */
    public function getSuccess()
    {
        $message = $this->getSuccessMessage();
        $this->unsSuccessMessage();
        return $message;
    }
}
