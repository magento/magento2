<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Block\Adminhtml\Attribute\Edit;

/**
 * Eav Attribute Block with additional js scripts in template
 */
class Js extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Eav::attribute/edit/js.phtml';

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype
     */
    private $inputtype;

    /**
     * @inheritDoc
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype $inputtype,
        array $data = []
    ) {
        $this->inputtype = $inputtype;
        parent::__construct($context, $data);
    }

    /**
     * Return compatible input types
     *
     * @deprecated 102.0.0 Misspelled method
     * @see getCompatibleInputTypes
     */
    public function getComaptibleInputTypes()
    {
        return $this->getCompatibleInputTypes();
    }

    /**
     * Get compatible input types.
     *
     * @return array
     */
    public function getCompatibleInputTypes()
    {
        return $this->inputtype->getVolatileInputTypes();
    }

    /**
     * Get hints on input types.
     *
     * @return array
     */
    public function getInputTypeHints()
    {
        return $this->inputtype->getInputTypeHints();
    }
}
