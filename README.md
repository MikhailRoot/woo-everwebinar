# woo-everwebinar
WooCommerce plugin to sell Everwebinar webinars of WebinarJam.

## How it works?
 You create webinars and products for them to sell on your wordpress+woocommerce website and sell them as usuall virtual products.
 
 Customer will receive Everwebinar access links as he pays for it via email. 
 
 And of course payment methods could be all that WooCommerce supports.
 
 You as admin will receive same links to your admin email to make sure you'll not lose this sensitive information due to technical problems and email delivery issues.


## Installation
1. install plugin into your Wordpress+WooCommerce website as usuall by uploading zip archive to Wordpress plugins installation dialog, and activate it.
2. go to **Woocommerce** ->  **Settings** -> **Products** -> **Everwebinar**
3. paste you API key for [everwebinar](https://app.webinarjam.com/login) and save settings (you need this to be done only once, as API key is not changing).
4. You need to create your webinar in [webinarjam everwebinar control panel](https://app.webinarjam.com/login)
5. Go to Products page in WooCommerce and click **Add Product**.
6. in Product type selection dropdown list will be a new item - **EverWebinar** - just select it
7. there will be product settings tab **Select webinar** and dropdown list to select your already created in [webinarjam.com panel](https://app.webinarjam.com/members/login) webinars. Choose which you want to sell.
8. Set price for this webinar, add description, photo or anything you need to specify like for usual woocommerce product and click **Publish**.
10. In **WooCommerce** -> **Settings** -> **Emails** you'll see 3 new email types, you can set desired templates and settings for them as necessary
  * `Registered to Webinar` - one for administrator and another for Customer
  * `Registered to Webinar Error Email` - email sent to administrator in case error happens while registering customer to webinar.
 