<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Newsletter\Model;

/**
 * Newsletter session model
 */
class Session extends \Magento\Framework\Session\Generic
{
    /**
     * Set error message
     *
     * @param string $message
     * @return $this
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
     */
    public function getSuccess()
    {
        $message = $this->getSuccessMessage();
        $this->unsSuccessMessage();
        return $message;
    }
}
