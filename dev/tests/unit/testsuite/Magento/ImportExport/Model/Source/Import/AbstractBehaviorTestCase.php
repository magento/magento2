<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract class for behavior tests
 */
namespace Magento\ImportExport\Model\Source\Import;

abstract class AbstractBehaviorTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Model for testing
     *
     * @var \Magento\ImportExport\Model\Source\Import\AbstractBehavior
     */
    protected $_model;

    protected function tearDown()
    {
        unset($this->_model);
    }
}
