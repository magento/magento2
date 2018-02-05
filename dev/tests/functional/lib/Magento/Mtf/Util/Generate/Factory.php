<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate;

use Magento\Framework\App;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory classes generator.
 *
 * @deprecated
 */
class Factory extends AbstractGenerate
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @constructor
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Generate Handlers.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        $this->objectManager->create('Magento\Mtf\Util\Generate\Factory\Block')->launch();
        $this->objectManager->create('Magento\Mtf\Util\Generate\Factory\Fixture')->launch();
        $this->objectManager->create('Magento\Mtf\Util\Generate\Factory\Handler')->launch();
        $this->objectManager->create('Magento\Mtf\Util\Generate\Factory\Page')->launch();
        $this->objectManager->create('Magento\Mtf\Util\Generate\Factory\Repository')->launch();

        return $this->objectManager->get('Magento\Framework\App\ResponseInterface');
    }

    /**
     * Generate single class.
     *
     * @param string $className
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function generate($className)
    {
        return false;
    }
}
