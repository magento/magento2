<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

interface BuilderInterface extends SimpleBuilderInterface
{
    /**
     * Set custom attribute value.
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @return $this
     */
    public function setCustomAttribute($attributeCode, $attributeValue);

    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException If array elements are not of AttributeValue type
     */
    public function setCustomAttributes(array $attributes);

    /**
     * Return created ExtensibleDataInterface object
     *
     * @return \Magento\Framework\Api\ExtensibleDataInterface
     */
    public function create();

    /**
     * Populates the fields with data from the array.
     *
     * Keys for the map are snake_case attribute/field names.
     *
     * @param array $data
     * @return $this
     */
    public function populateWithArray(array $data);

    /**
     * Populates the fields with an existing entity.
     *
     * @param ExtensibleDataInterface $prototype the prototype to base on
     * @return $this
     * @throws \LogicException If $prototype object class is not the same type as object that is constructed
     */
    public function populate(ExtensibleDataInterface $prototype);

    /**
     * Populate builder with the two data interfaces, merging them
     *
     * @param ExtensibleDataInterface $firstDataObject
     * @param ExtensibleDataInterface $secondDataObject
     * @return $this
     * @throws \LogicException
     */
    public function mergeDataObjects(
        ExtensibleDataInterface $firstDataObject,
        ExtensibleDataInterface $secondDataObject
    );

    /**
     * Populate builder with the data interface and array, merging them
     *
     * @param ExtensibleDataInterface $dataObject
     * @param array $data
     * @return $this
     * @throws \LogicException
     */
    public function mergeDataObjectWithArray(ExtensibleDataInterface $dataObject, array $data);
}
