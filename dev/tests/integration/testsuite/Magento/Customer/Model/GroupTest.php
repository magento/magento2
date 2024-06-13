<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    protected $groupModel;

    /**
     * @var GroupInterfaceFactory
     */
    protected $groupFactory;

    protected function setUp(): void
    {
        $this->groupModel = Bootstrap::getObjectManager()->create(Group::class);
        $this->groupFactory = Bootstrap::getObjectManager()->create(GroupInterfaceFactory::class);
    }

    public function testCRUD()
    {
        $this->groupModel->setCode('test');
        $crud = new \Magento\TestFramework\Entity($this->groupModel, ['customer_group_code' => uniqid()]);
        $crud->testCrud();
    }

    /**
     * Test that customer group correctly handles multibyte and normal characters when saving
     *
     * This verifies that the fix for multibyte character truncation works correctly.
     * Previously, substr() was used which counted bytes instead of characters,
     * causing multibyte characters to be truncated incorrectly.
     *
     * @magentoDbIsolation enabled
     * @dataProvider customerGroupCodeDataProvider
     * @param string $code
     * @param string $expectedCode
     * @param int $charLength
     * @return void
     * @throws LocalizedException
     */
    public function testMultibyteAndNormalCharacterHandling(string $code, string $expectedCode, int $charLength): void
    {
        $this->groupModel->setCode($code);
        $this->groupModel->setTaxClassId(3);
        $group = $this->groupModel->save();

        // Reload from database
        $reloadedGroup = $this->groupModel->load($group->getId());

        // Verify all 32 characters are preserved
        $this->assertEquals(
            $expectedCode,
            $reloadedGroup->getCode(),
            'Group code with multibyte and normal characters should be saved correctly'
        );

        $this->assertEquals(
            $charLength,
            mb_strlen($reloadedGroup->getCode()),
            'Group code should have maximum 32 characters'
        );

        // Cleanup
        $reloadedGroup->delete();
    }

    /**
     * Customer group code data provider
     *
     * @return array[]
     */
    public static function customerGroupCodeDataProvider(): array
    {
        // Test with multibyte characters (ö = 2 bytes in UTF-8)
        $multibyteString = str_repeat('ö', 32); // 31 characters, 62 bytes
        $normalString = str_repeat('a', 50); // 40 characters, will be truncated
        $normalTruncatedString = str_repeat('a', 32); // 31 characters, truncated code after saving
        $mixedString = str_repeat('a', 10).str_repeat('ö', 10);
        return [
            'multibyte characters' => [
                $multibyteString,
                $multibyteString,
                32
            ],
            'normal characters' => [
                $normalString,
                $normalTruncatedString,
                32
            ],
            'mixed characters' => [
                $mixedString,
                $mixedString,
                20
            ]
        ];
    }
}
