@javascript
Feature: Update product feature

  Scenario: Update product (admin side)

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

    And I wait for element with xpath "//*[@id='menu-magento-catalog-catalog']/a" to appear
    And I click on the element with xpath "//*[@id='menu-magento-catalog-catalog']/a"

    And I wait for element with xpath "//*[@id='menu-magento-catalog-catalog']/div/ul/li/div/ul/li[1]/a" to appear
    And I click on the element with xpath "//*[@id='menu-magento-catalog-catalog']/div/ul/li/div/ul/li[1]/a"

    #Catalog
    And I wait for element with xpath "//*[@id='container']/div/div[4]/table/tbody/tr[3]/td[13]/a" to appear
    And I click on the element with xpath "//*[@id='container']/div/div[4]/table/tbody/tr[3]/td[13]/a"

    #Edit Item (Crown Summit Backpack)
    And I wait for element with xpath "//*[@id='container']/div/div[2]/div[1]/div/fieldset/div[1]/label/span" to appear
    And I fill in the following:
      | product[name]                                         | Crown Summit Backpack Black        |
      | product[price]                                        | 60.00                              |
      | product[quantity_and_stock_status][qty]               | 135                                |
    And I select "Out of Stock" from "product[quantity_and_stock_status][is_in_stock]"
    And I fill in the following:
      | product[weight]                                       | 0.005                              |

    And I select "China" from "product[country_of_manufacture]"
    And I select "Climbing" from "product[activity]"
    And I select "Microfiber" from "product[material]"
    And I select "Orange" from "product[color]"
    And I select "Shoulder" from "product[strap_bags]"
    And I select "Laptop Sleeve" from "product[features_bags]"

    And I wait for element with xpath "//*[@id='container']/div/div[2]/div[1]/div/fieldset/div[15]/div/div/label" to appear
    And I click on the element with xpath "//*[@id='container']/div/div[2]/div[1]/div/fieldset/div[15]/div/div/label"

    And I click on the element with xpath "//*[@id='container']/div/div[2]/div[1]/div/fieldset/div[16]/div/div/label"
    And I click on the element with xpath "//*[@id='container']/div/div[2]/div[1]/div/fieldset/div[17]/div/div/label"
    And I click on the element with xpath "//*[@id='container']/div/div[2]/div[1]/div/fieldset/div[18]/div/div/label"
    And I click on the element with xpath "//*[@id='container']/div/div[2]/div[1]/div/fieldset/div[19]/div/div/label"
    And I click on the element with xpath "//*[@id='container']/div/div[2]/div[1]/div/fieldset/div[16]/div/div/label"

    And I wait for element with xpath "//*[@id='save-button']/span" to appear
    And I click on the element with xpath "//*[@id='save-button']/span"

    And I wait for element with xpath "//*[@id='messages']/div/div/div" to appear
