<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

/**
 * @api
 * @since 2.0.0
 */
interface ElementInterface
{
    /**
     * Set element data
     *
     * @param array $data
     * @param string $scope
     * @return void
     * @since 2.0.0
     */
    public function setData(array $data, $scope);

    /**
     * Retrieve element configuration
     *
     * @return array
     * @since 2.0.0
     */
    public function getData();

    /**
     * Retrieve element id
     *
     * @return string
     * @since 2.0.0
     */
    public function getId();

    /**
     * Retrieve element label
     *
     * @return string
     * @since 2.0.0
     */
    public function getLabel();

    /**
     * Check whether element is visible
     *
     * @return bool
     * @since 2.0.0
     */
    public function isVisible();

    /**
     * Retrieve arbitrary element attribute
     *
     * @param string $key
     * @return mixed
     * @since 2.0.0
     */
    public function getAttribute($key);
}
