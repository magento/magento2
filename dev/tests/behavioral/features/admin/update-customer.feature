@javascript
Feature: Update customer feature

  Scenario: Update customer (admin side)

    Given I am on "http://magento.vm/index.php/admin/admin/index/index/key/509d07fe462fb9526fa8419f968a4373fdb9e162fb47fd7845b3de2f8d550be4/"

    Then I wait for element with xpath "//*[@id='html-body']/section" to appear
    And I fill in the following:
      | login[username]           | demo                |
      | login[password]           | demoPwd0            |

    And I wait for element with xpath "//*[@id='login-form']/fieldset/div[3]/div[1]/button/span" to appear
    And I click on the element with xpath "//*[@id='login-form']/fieldset/div[3]/div[1]/button/span"

    #Customers
    And I wait for element with xpath "//*[@id='menu-magento-customer-customer']/a" to appear
    And I click on the element with xpath "//*[@id='menu-magento-customer-customer']/a"

    And I wait for element with xpath "//*[@id='menu-magento-customer-customer']/div/ul/li[1]/a/span" to appear
    And I click on the element with xpath "//*[@id='menu-magento-customer-customer']/div/ul/li[1]/a/span"

    #Edit Customers
    And I wait for element with xpath "//*[@id='container']/div/div[5]/table/tbody/tr[2]/td[17]/a" to appear
    And I click on the element with xpath "//*[@id='container']/div/div[5]/table/tbody/tr[2]/td[17]/a"

    And I wait for element with xpath "//*[@id='tab_customer']/span[1]" to appear
    And I click on the element with xpath "//*[@id='tab_customer']/span[1]"
  
    #Account Information
    And I wait for element with xpath "//*[@id='container']/div/div/div[2]/div[1]/div/div[2]/fieldset/fieldset/div/div[1]/label/span" to appear
    And I select "Retailer" from "customer[group_id]"

    And I check "customer[disable_auto_group_change]"
    And I fill in the following:
      | customer[prefix]           | 123456789                |
      | customer[middlename]       | Anabella                 |
      | customer[suffix]           | AL                       |

    And I fill in the following:
      | customer[taxvat]           | 123456789012             |
    And I select "Female" from "customer[gender]"

    #Wish List
    And I wait for element with xpath "//*[@id='tab_block_wishlist']/span[1]" to appear
    And I click on the element with xpath "//*[@id='tab_block_wishlist']/span[1]"

    And I wait for element with xpath "//*[@id='wishlistGrid_table']/thead/tr[1]/th[1]/span" to appear
    And I fill in the following:
      | product_name           | Compete Track Tote             |
      | description            | Daytrip Backpack               |
      | qty[from]              | 10.000                         |
      | qty[to]                | 1000.000                       |
      | days[from]             | 10                             |
      | days[to]               | 365                            |


    And I wait for element with xpath "//*[@id='save']" to appear
    And I click on the element with xpath "//*[@id='save']"

    And I wait for element with xpath "//*[@id='messages']/div/div/div" to appear
