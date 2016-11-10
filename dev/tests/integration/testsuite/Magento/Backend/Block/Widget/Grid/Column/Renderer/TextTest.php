<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;
use Magento\User\Model\User;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Locale\Manager;
use Magento\Framework\TranslateInterface;

/**
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class TextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var User $user */
        $user = $this->objectManager->get(User::class);
        /** @var Session $session */
        $session = $this->objectManager->get(Session::class);
        $session->setUser($user);
        /** @var Manager $localeManager */
        $localeManager = $this->objectManager->get(Manager::class);
        $localeManager->switchBackendInterfaceLocale('fr_FR');
        /** @var TranslateInterface $translate */
        $translate = $this->objectManager->get(TranslateInterface::class);
        $translate->loadData(null, true);
    }

    /**
     * @param array $columnData
     * @param array $rowData
     * @param string $expected
     * @dataProvider renderDataProvider
     */
    public function testRender($columnData, $rowData, $expected)
    {
        /** @var Text $renderer */
        $renderer = $this->objectManager->create(Text::class);
        /** @var Column $column */
        $column = $this->objectManager->create(
            Column::class,
            [
                'data' => $columnData
            ]
        );
        /** @var DataObject $row */
        $row = $this->objectManager->create(
            DataObject::class,
            [
                'data' => $rowData
            ]
        );
        $this->assertEquals(
            $expected,
            $renderer->setColumn($column)->render($row)
        );
    }

    /**
     * @return array
     */
    public function renderDataProvider()
    {
        return [
            [
                [
                    'index' => 'title',
                    'translate' => true
                ],
                [
                    'title' => 'Need to be translated'
                ],
                'Translated'
            ],
            [
                [
                    'index' => 'title'
                ],
                [
                    'title' => 'Doesn\'t need to be translated'
                ],
                'Doesn&#039;t need to be translated'
            ],
            [
                [
                    'format' => '#$subscriber_id $customer_name ($subscriber_email)'
                ],
                [
                    'subscriber_id' => '10',
                    'customer_name' => 'John Doe',
                    'subscriber_email' => 'john@doe.com'
                ],
                '#10 John Doe (john@doe.com)'
            ],
            [
                [
                    'format' => 'Name: $customer_name, email: $subscriber_email',
                    'translate' => true
                ],
                [
                    'customer_name' => 'John Doe',
                    'subscriber_email' => 'john@doe.com'
                ],
                'Nom: John Doe, email: john@doe.com'
            ],
            [
                [
                    'format' => 'Need to be translated',
                    'translate' => true
                ],
                [],
                'Translated'
            ],
            [
                [
                    'format' => 'Doesn\'t need to be translated'
                ],
                [],
                'Doesn&#039;t need to be translated'
            ]
        ];
    }
}
