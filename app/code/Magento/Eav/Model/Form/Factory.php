<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Form;

/**
 * EAV form object factory
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @codeCoverageIgnore
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
