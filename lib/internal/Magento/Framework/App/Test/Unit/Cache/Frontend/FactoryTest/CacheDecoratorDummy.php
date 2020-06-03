<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Dummy object to test creation of decorators for cache frontend
 */
namespace Magento\Framework\App\Test\Unit\Cache\Frontend\FactoryTest;

use Magento\Framework\Cache\Frontend\Decorator\Bare;
use Magento\Framework\Cache\FrontendInterface;

class CacheDecoratorDummy extends Bare
{
    /**
     * @var array
     */
    protected $_params;

    /**
     * @param FrontendInterface $frontend
     * @param array $params
     */
    public function __construct(FrontendInterface $frontend, array $params)
    {
        parent::__construct($frontend);
        $this->_params = $params;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }
}
