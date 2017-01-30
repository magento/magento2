<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Structure\Element;

class FieldPlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_request = $request;
    }

    /**
     * Get original configPath (not changed by PayPal configuration inheritance)
     *
     * @param \Magento\Config\Model\Config\Structure\Element\Field $subject
     * @param \Closure $proceed
     * @return string|null
     */
    public function aroundGetConfigPath(
        \Magento\Config\Model\Config\Structure\Element\Field $subject,
        \Closure $proceed
    ) {
        $configPath = $proceed();
        if (!isset($configPath) && $this->_request->getParam('section') == 'payment') {
            $configPath = preg_replace('@^(' . implode(
                '|',
                \Magento\Paypal\Model\Config\StructurePlugin::getPaypalConfigCountries(true)
            ) . ')/@', 'payment/', $subject->getPath());
        }
        return $configPath;
    }
}
