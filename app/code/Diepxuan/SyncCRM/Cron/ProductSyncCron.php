<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-03-31 21:40:11
 */

namespace Diepxuan\SyncCRM\Cron;

use Diepxuan\SyncCRM\Sync\ProductSync as Sync;
use Psr\Log\LoggerInterface;

class ProductSyncCron
{
    protected $logger;
    protected $sync;

    public function __construct(LoggerInterface $logger, Sync $sync)
    {
        $this->logger = $logger;
        $this->sync   = $sync;
    }

    public function execute(): void
    {
        try {
            $this->sync->sync();
        } catch (\Exception $e) {
            $this->logger->error('❌ Lỗi đồng bộ sản phẩm: ' . $e->getMessage());
        }
    }
}
