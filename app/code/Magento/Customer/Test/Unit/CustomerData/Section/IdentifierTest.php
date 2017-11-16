<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\CustomerData\Section;

use \Magento\Customer\CustomerData\Section\Identifier;

class IdentifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\CustomerData\Section\Identifier
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookieManMock;

    /**
     * @var string
     */
    protected $cookieMarkId;

    protected function setUp()
    {
        $this->cookieManMock = $this->createMock(\Magento\Framework\Stdlib\Cookie\PhpCookieManager::class);
        $this->cookieMarkId = '123456';
        $this->model = new Identifier(
            $this->cookieManMock
        );
    }

    public function testInitMark()
    {
        $this->cookieManMock->expects($this->once())
            ->method('getCookie')
            ->with(Identifier::COOKIE_KEY)
            ->willReturn($this->cookieMarkId);
        $this->assertEquals($this->cookieMarkId, $this->model->initMark(false));
    }

    public function testMarkSectionsDontUpdate()
    {
        $sectionsData = [
            'section1' => [1],
            'section2' => [2],
            'section3' => [3],
        ];

        $expectedData = [
            'section1' => [1, 'data_id' => $this->cookieMarkId],
            'section2' => [2, 'data_id' => $this->cookieMarkId],
            'section3' => [3],
        ];
        $sectionNames = ['section1', 'section2'];

        $this->cookieManMock->expects($this->once())
            ->method('getCookie')
            ->with(Identifier::COOKIE_KEY)
            ->willReturn($this->cookieMarkId);

        // third parameter is true to avoid diving deeply into initMark()
        $result = $this->model->markSections($sectionsData, $sectionNames, false);
        $this->assertEquals($expectedData, $result);
    }

    public function testMarkSectionsUpdate()
    {
        $sectionsData = [
            'section1' => [1, 'data_id' => 0],
            'section2' => [2, 'data_id' => 0],
            'section3' => [3],
        ];
        $sectionNames = ['section1', 'section2'];

        // third parameter is true to avoid diving deeply into initMark()
        $result = $this->model->markSections($sectionsData, $sectionNames, true);
        $this->assertArrayHasKey('data_id', $result['section1']);
        $this->assertNotEquals(0, $result['section1']['data_id']);
        $this->assertArrayHasKey('data_id', $result['section2']);
        $this->assertNotEquals(0, $result['section2']['data_id']);
    }
}
