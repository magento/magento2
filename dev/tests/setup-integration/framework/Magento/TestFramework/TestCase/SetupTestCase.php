<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\TestCase;

use Magento\Framework\App\DesignInterface;
use Magento\Framework\View\DesignExceptions;

/**
 * Instance of Setup test case. Used in order to tweak dataProviders functionality.
 */
class SetupTestCase extends \PHPUnit\Framework\TestCase implements MutableDataInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @inheritdoc
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function flushData()
    {
        $this->data = [];
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }
}
