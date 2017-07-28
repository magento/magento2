<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Form;

/**
 * EAV form object factory
 * @api
 * @since 2.0.0
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new form object
     *
     * @param string $form
     * @param array $data
     * @throws \InvalidArgumentException
     * @return \Magento\Eav\Model\Form
     * @since 2.0.0
     */
    public function create($form, array $data = [])
    {
        $formInstance = $this->_objectManager->create($form, $data);
        if (false == $formInstance instanceof \Magento\Eav\Model\Form) {
            throw new \InvalidArgumentException($form . ' is not instance of \Magento\Eav\Model\Form');
        }
        return $formInstance;
    }
}
