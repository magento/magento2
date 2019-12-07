<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Item;

/**
 * Class Validator
 *
 * @package Magento\Backend\Model\Menu\Item
 * @api
 * @since 100.0.2
 */
class Validator
{
    /**
     * The list of required params
     *
     * @var string[]
     */
    protected $_required = ['id', 'title', 'resource'];

    /**
     * List of created item ids
     *
     * @var array
     */
    protected $_ids = [];

    /**
     * The list of primitive validators
     *
     * @var \Zend_Validate[]
     */
    protected $_validators = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $idValidator = new \Zend_Validate();
        $idValidator->addValidator(new \Zend_Validate_StringLength(['min' => 3]));
        $idValidator->addValidator(new \Zend_Validate_Regex('/^[A-Za-z0-9\/:_]+$/'));

        $resourceValidator = new \Zend_Validate();
        $resourceValidator->addValidator(new \Zend_Validate_StringLength(['min' => 8]));
        $resourceValidator->addValidator(
            new \Zend_Validate_Regex('/^[A-Z][A-Za-z0-9]+_[A-Z][A-Za-z0-9]+::[A-Za-z_0-9]+$/')
        );

        $attributeValidator = new \Zend_Validate();
        $attributeValidator->addValidator(new \Zend_Validate_StringLength(['min' => 3]));
        $attributeValidator->addValidator(new \Zend_Validate_Regex('/^[A-Za-z0-9\/_\-]+$/'));

        $textValidator = new \Zend_Validate_StringLength(['min' => 3, 'max' => 50]);

        $titleValidator = $tooltipValidator = $textValidator;
        $actionValidator = $moduleDepValidator = $configDepValidator = $attributeValidator;

        $this->_validators['id'] = $idValidator;
        $this->_validators['title'] = $titleValidator;
        $this->_validators['action'] = $actionValidator;
        $this->_validators['resource'] = $resourceValidator;
        $this->_validators['dependsOnModule'] = $moduleDepValidator;
        $this->_validators['dependsOnConfig'] = $configDepValidator;
        $this->_validators['toolTip'] = $tooltipValidator;
    }

    /**
     * Validate menu item params
     *
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function validate($data)
    {
        if ($this->checkMenuItemIsRemoved($data)) {
            return;
        }

        $this->assertContainsRequiredParameters($data);
        $this->assertIdentifierIsNotUsed($data['id']);

        foreach ($data as $param => $value) {
            $this->validateMenuItemParameter($param, $value);
        }
        $this->_ids[] = $data['id'];
    }

    /**
     * Check that menu item is not deleted
     *
     * @param array $data
     * @return bool
     */
    private function checkMenuItemIsRemoved($data)
    {
        return isset($data['id'], $data['removed']) && $data['removed'] === true;
    }

    /**
     * Check that menu item contains all required data
     *
     * @param array $data
     *
     * @throws \BadMethodCallException
     */
    private function assertContainsRequiredParameters($data)
    {
        foreach ($this->_required as $param) {
            if (!isset($data[$param])) {
                throw new \BadMethodCallException('Missing required param ' . $param);
            }
        }
    }

    /**
     * Check that menu item id is not used
     *
     * @param string $id
     * @throws \InvalidArgumentException
     */
    private function assertIdentifierIsNotUsed($id)
    {
        if (array_search($id, $this->_ids) !== false) {
            throw new \InvalidArgumentException('Item with id ' . $id . ' already exists');
        }
    }

    /**
     * Validate menu item parameter value
     *
     * @param string $param
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    private function validateMenuItemParameter($param, $value)
    {
        if ($value === null) {
            return;
        }
        if (!isset($this->_validators[$param])) {
            return;
        }

        $validator = $this->_validators[$param];
        if ($validator->isValid($value)) {
            return;
        }

        throw new \InvalidArgumentException(
            "Param " . $param . " doesn't pass validation: " . implode(
                '; ',
                $validator->getMessages()
            )
        );
    }

    /**
     * Validate incoming param
     *
     * @param string $param
     * @param mixed $value
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateParam($param, $value)
    {
        if (in_array($param, $this->_required) && $value === null) {
            throw new \InvalidArgumentException('Param ' . $param . ' is required');
        }

        if ($value !== null && isset($this->_validators[$param]) && !$this->_validators[$param]->isValid($value)) {
            throw new \InvalidArgumentException(
                'Param ' . $param . ' doesn\'t pass validation: ' . implode(
                    '; ',
                    $this->_validators[$param]->getMessages()
                )
            );
        }
    }
}
