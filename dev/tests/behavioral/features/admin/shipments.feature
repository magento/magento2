@javascript
Feature: Shipments feature

  Scenario: Shipments (admin side)

    Given I am on "http://magento.vm/index.php/admin/admin/index/index/key/509d07fe462fb9526fa8419f968a4373fdb9e162fb47fd7845b3de2f8d550be4/"

    Then I wait for element with xpath "//*[@id='html-body']/section" to appear
    And I fill in the following:
      | login[username]           | demo                |
      | login[password]           | demoPwd0            |

    And I wait for element with xpath "//*[@id='login-form']/fieldset/div[3]/div[1]/button/span" to appear
    And I click on the element with xpath "//*[@id='login-form']/fieldset/div[3]/div[1]/button/span"

    #Popup msg
    And I wait for element with xpath "//*[@id='html-body']/div[4]/aside/div[2]/header/button" to appear
    And I click on the element with xpath "//*[@id='html-body']/div[4]/aside/div[2]/header/button"

    #Sales
    And I wait for element with xpath "//*[@id='menu-magento-sales-sales']/a" to appear
    And I click on the element with xpath "//*[@id='menu-magento-sales-sales']/a"

    And I wait for element with xpath "//*[@id='menu-magento-sales-sales']/div/ul/li/div/ul/li[3]/a/span" to appear
    And I click on the element with xpath "//*[@id='menu-magento-sales-sales']/div/ul/li/div/ul/li[3]/a/span"

    #Shipments
    And I wait for element with xpath "//*[@id='container']/div/div[3]/table/tbody/tr[1]/td[8]/a" to appear
    And I click on the element with xpath "//*[@id='container']/div/div[3]/table/tbody/tr[1]/td[8]/a"

    And I wait for element with xpath "//*[@id='container']/section[2]/div[2]/div[1]/div/div/a" to appear
    And I click on the element with xpath "//*[@id='container']/section[2]/div[2]/div[1]/div/div/a"

    #Change address
    And I wait for element with xpath "//*[@id='container']/fieldset/legend/span" to appear
    And I fill in the following:
      | prefix                    | 123456789                |
      | company                   | TestTest                 |
      | street[0]                 | 7865 Main street         |
    And I select "Florida" from "region_id"
    And I fill in the following:
      | postcode                  | 121212-1212              |
      | telephone                 | (555) 111-1111           |
      | fax                       | (555) 999-0000           |

    And I wait for element with xpath "//*[@id='save']/span/span" to appear
    And I click on the element with xpath "//*[@id='save']/span/span"
    And I wait for element with xpath "//*[@id='messages']/div/div/div" to appear

    #Invoices
    And I wait for element with xpath "//*[@id='sales_order_view_tabs_order_invoices']/span[1]" to appear
    And I click on the element with xpath "//*[@id='sales_order_view_tabs_order_invoices']/span[1]"

    And I wait for element with xpath "//*[@id='sales_order_view_tabs_order_invoices_content']/div/div[3]/table/tbody/tr/td[9]/a" to appear
    And I click on the element with xpath "//*[@id='sales_order_view_tabs_order_invoices_content']/div/div[3]/table/tbody/tr/td[9]/a"

    And I wait for element with xpath "//*[@id='history_comment']" to appear
    And I fill in the following:
      | comment[comment]                  | Very important. Thank you. |
    And I check "comment[is_customer_notified]"

    And I wait for element with xpath "//*[@id='submit_comment_button']/span" to appear
    And I click on the element with xpath "//*[@id='submit_comment_button']/span"

    And I wait for element with xpath "//*[@id='comments_block']/ul/li[1]/div" to appear
