<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Setup\AttributeConfiguration\Provider;

use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator;
use Magento\Eav\Setup\AttributeConfiguration\InvalidConfigurationException;

class FrontendInputUiProvider implements ProviderInterface
{
    /**
     * @var Validator
     */
    private $uiTypeValidator;

    /**
     * @param Validator $uiTypeValidator
     */
    public function __construct(Validator $uiTypeValidator)
    {
        $this->uiTypeValidator = $uiTypeValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($type)
    {
        return $this->uiTypeValidator->isValid($type);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($type)
    {
        if (!$this->exists($type)) {
            throw new InvalidConfigurationException(__('Frontend input type "%1" is not supported', $type));
        }

        return $type;
    }
}
