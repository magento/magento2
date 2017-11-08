@javascript
Feature: Product purchase

  Scenario: Purchase products

    Given I am on "/"

    Then I wait for element with xpath "//*[@id='ui-id-6']/span[2]" to appear
    And I click on the element with xpath "//*[@id='ui-id-6']/span[2]"

    Then I wait for page to load "/index.php/women.html"
    And I wait for element with xpath "//*[@id='maincontent']/div[4]/div[2]/div/div/ul[1]/li[1]/a" to appear
    And I click on the element with xpath "//*[@id='maincontent']/div[4]/div[2]/div/div/ul[1]/li[1]/a"

    #Choosing 1 item
    And I wait for element with xpath "//*[@id='maincontent']/div[3]/div[1]/div[3]/ol/li[3]/div/a/span/span/img" to appear
    And I click on the element with xpath "//*[@id='maincontent']/div[3]/div[1]/div[3]/ol/li[3]/div/a/span/span/img"
    And I wait for page to load "/index.php/autumn-pullie.html"

    And I wait for element with xpath "//*[@id='product-options-wrapper']/div/div/div[1]/div/div[3]" to appear
    And I click on the element with xpath "//*[@id='product-options-wrapper']/div/div/div[1]/div/div[3]"

    And I wait for element with xpath "//*[@id='product-options-wrapper']/div/div/div[2]/div/div[1]" to appear
    And I click on the element with xpath "//*[@id='product-options-wrapper']/div/div/div[2]/div/div[1]"

    #Add to card
    And I wait for element with xpath "//*[@id='product-addtocart-button']/span" to appear
    And I click on the element with xpath "//*[@id='product-addtocart-button']/span"

    #Check the card
    And I wait for element with xpath "//div[1]/header/div[2]/div[1]/a/span[2]" to appear
    And I click on the element with xpath "//div[1]/header/div[2]/div[1]/a/span[2]"

    And I wait for element with xpath "//*[@id='top-cart-btn-checkout']" to appear
    And I click on the element with xpath "//*[@id='top-cart-btn-checkout']"

    #Checkout
    And I wait for page to load "/index.php/checkout/"
    And I wait for element with xpath "//*[@id='customer-email']" to appear

    And I fill in the following:
      | customer-email     | test@test.com   |
      | firstname          | Peter           |
      | lastname           | Smith           |
      | company            | AAA             |
      | street[0]          | 5 Main street   |
      | city               | King            |

    And I select "Florida" from "region_id"
    And I fill in the following:
      | postcode           | 12345           |
    And I select "United States" from "country_id"
    And I fill in the following:
      | telephone          | 4166666666      |
    And I click on the element with xpath "//*[@id='s_method_flatrate_flatrate']"

    And I wait for element with xpath "//*[@id='shipping-method-buttons-container']/div/button" to appear
    And I click on the element with xpath "//*[@id='shipping-method-buttons-container']/div/button"

    #Payment
    Then I wait for page to load "/index.php/checkout/#payment"
    And I wait for element with xpath "//*[@id='checkout-payment-method-load']/div/div/div[2]/div[2]/div[4]/div/button/span" to appear
    And I click on the element with xpath "//*[@id='checkout-payment-method-load']/div/div/div[2]/div[2]/div[4]/div/button/span"

    #Success
    Then I wait for page to load "/index.php/checkout/onepage/success/"
    And I wait for element containing unique text "Your order # is" to appear