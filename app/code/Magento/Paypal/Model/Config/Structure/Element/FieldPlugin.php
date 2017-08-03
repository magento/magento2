<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Structure\Element;

use Magento\Framework\App\RequestInterface;
use Magento\Config\Model\Config\Structure\Element\Field as FieldConfigStructure;
use Magento\Paypal\Model\Config\StructurePlugin as ConfigStructurePlugin;

/**
 * Plugin for \Magento\Config\Model\Config\Structure\Element\Field
 */
class FieldPlugin
{
    /**
     * @var RequestInterface
     * @since 2.2.0
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Get original configPath (not changed by PayPal configuration inheritance)
     *
     * @param FieldConfigStructure $subject
     * @param string|null $result
     * @return string|null
     * @since 2.2.0
     */
    public function afterGetConfigPath(FieldConfigStructure $subject, $result)
    {
        if (!$result && $this->request->getParam('section') == 'payment') {
            $result = preg_replace(
                '@^(' . implode('|', ConfigStructurePlugin::getPaypalConfigCountries(true)) . ')/@',
                'payment/',
                $subject->getPath()
            );
        }

        return $result;
    }
}
