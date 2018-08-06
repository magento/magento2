<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Developer\Model\HttpMethodUpdater;

use Magento\Framework\App\Request\HttpMethodMap;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class UpdaterTest extends TestCase
{
    /**
     * @var Updater
     */
    private $updater;

    /**
     * @var HttpMethodMap
     */
    private $map;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->updater = $objectManager->get(Updater::class);
        $this->map = $objectManager->get(HttpMethodMap::class);
    }

    /**
     * @param int $index
     *
     * @return string
     */
    private function prepareFakeAction(int $index): string
    {
        $file = __DIR__ ."/../../_files/fake_action$index.php";
        $tmp = $file .'tmp';
        $copied = @copy(
            $file,
            $tmp
        );
        if (!$copied) {
            throw new \RuntimeException("Failed to copy $file");
        }
        include $tmp;

        return 'FakeNamespace\\FakeSubNamespace\\FakeAction' .($index === 1? '' : $index);
    }

    /**
     * @param int $index
     *
     * @return string
     */
    private function readUpdated(int $index): string
    {
        $classIndex = $index === 1? '' : $index;
        $tmp =  __DIR__ ."/../../_files/fake_action$index.phptmp";
        $updated = $tmp .'updated';
        $copied = @copy($tmp, $updated);
        if (!$copied) {
            throw new \RuntimeException("Failed to copy $tmp");
        }
        $updatedContent = file_get_contents($updated);
        if ($updatedContent === false) {
            throw new \RuntimeException("Cannot read $updated");
        }
        $wrote = file_put_contents(
            $updated,
            str_replace(
                "FakeAction$classIndex",
                $updatedName = "FakeAction{$classIndex}Updated",
                $updatedContent
            )
        );
        if (!$wrote) {
            throw new \RuntimeException("Failed to write $updated");
        }
        try {
            include $updated;
        } catch (\Throwable $exception) {
            throw new \RuntimeException("Failed to include $updated", 0, $exception);
        }

        return "FakeNamespace\\FakeSubNamespace\\$updatedName";
    }

    /**
     * @param int $index
     *
     * @return void
     */
    private function clean(int $index): void
    {
        $file = __DIR__ ."/../../_files/fake_action$index.php";
        unlink($file .'tmp');
        unlink($file .'tmpupdated');
    }

    /**
     * @param int $index
     * @param string[] $methods
     */
    private function tryFile(int $index, array $methods): void
    {
        $logged = new Logged($this->prepareFakeAction($index), $methods);

        $this->updater->update($logged);

        $updatedClass = $this->readUpdated($index);
        foreach ($methods as $method) {
            $this->assertContains(
                $this->map->getMap()[$method],
                class_implements($updatedClass, false)
            );
        }

        $this->clean($index);
    }

    public function testFile1()
    {
        $this->tryFile(1, ['POST']);
    }

    public function testFile2()
    {
        $this->tryFile(2, ['POST', 'PATCH']);
    }
}
