<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ConfigurableProduct\Api\Data;

interface OptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getAttributeId();

    /**
     * @return string|null
     */
    public function getLabel();

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @return int|null
     */
    public function getPosition();

    /**
     * @return bool|null
     */
    public function getIsUseDefault();

    /**
     * @return \Magento\ConfigurableProduct\Api\Data\OptionValueInterface[]|null
     */
    public function getValues();
}
