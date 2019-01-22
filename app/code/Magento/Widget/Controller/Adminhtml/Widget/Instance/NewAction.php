<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

class NewAction extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * New widget instance action (forward to edit action)
     *
     * @return void
     */
    public function execute(): void
    {
        $this->_forward('edit');
    }
}
