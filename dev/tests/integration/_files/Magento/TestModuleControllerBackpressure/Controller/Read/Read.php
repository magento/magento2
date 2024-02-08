<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestModuleControllerBackpressure\Controller\Read;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Response\Http as HttpResponse;

class Read extends Action
{
    /**
     * @var int
     */
    private int $counter = 0;

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var HttpResponse $response */
        $response = $this->getResponse();
        return $response->representJson('{"str": "controller-read", "counter": ' .(++$this->counter) .'}');
    }

    public function resetCounter(): void
    {
        $this->counter = 0;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }
}
