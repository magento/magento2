<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate;

/**
 * Factory classes generator.
 *
 * @deprecated
 */
class Factory extends AbstractGenerate
{
    /**
     * Generate Handlers.
     *
     * @return bool
     */
    public function launch()
    {
        $this->objectManager->create(\Magento\Mtf\Util\Generate\Factory\Block::class)->launch();
        $this->objectManager->create(\Magento\Mtf\Util\Generate\Factory\Fixture::class)->launch();
        $this->objectManager->create(\Magento\Mtf\Util\Generate\Factory\Handler::class)->launch();
        $this->objectManager->create(\Magento\Mtf\Util\Generate\Factory\Page::class)->launch();
        $this->objectManager->create(\Magento\Mtf\Util\Generate\Factory\Repository::class)->launch();

        return true;
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
