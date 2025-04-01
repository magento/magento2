<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-04-01 20:49:57
 */

namespace Diepxuan\SyncCRM\Cron;

use Diepxuan\SyncCRM\Sync\CategorySync as Sync;
use Psr\Log\LoggerInterface;

class CategorySyncCron
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
            $this->logger->error('❌ Lỗi đồng bộ danh mục: ' . $e->getMessage());
        }
    }
}
