# WORK IN PROGRESS 

# TransferWise Simple API PHP Class

## Introduction
This PHP class is completely standalone; it does not require composer to bring in other code. As such this is a light-weight module for easy inclusion into bigger projects.

It has been written for my own use, and as such does not include methods for all TransferWise [API calls](https://api-docs.transferwise.com/#transferwise-api), but I welcome contributors to add methods to add access to other API calls.

## Requirements
A webserver with PHP, that you create/save new PHP files.

## Security
NEVER save your API keys in the main code files. In this tutorial, they are saved in a separate *include* file. The API key in this tutorial is a *Limited Access* key; meaning if it is compromised the bad actor can not spend your money. You will need to use a *Full Access* API key if you need to spend money, etc.

## Summary of Steps

1. Transfer Wise
    1. Create Sandbox account (Personal)
        1. Create API Token - Read only
        1. Create API Token - Full access
    1. Create Production account (Personal, if not already created)
        1. Create API Token - Read only
        1. Create API Token - Full access
1. Your Web Server
    1. Create configure.php. Enter the API Tokens.
    1. Create class_TransferWise.php
    1. Create test.php
1. Your Web Browser
    1. Run test.php to get profileIDs
1. Your Web Server
    1. Update configure.php with these profileIds
1. Your Web Browser
    1. Run test.php to GET and POST information

## Step by Step

### 1. Transfer Wise
* Visit https://sandbox.transferwise.tech/
  * Register. Create a new account. Record your login details (email address/password)
  * Open Settings page. https://sandbox.transferwise.tech/user/settings
  * Add new token: Full access
  * Add new token: Read only
  * Logout

* Visit https://transferwise.com
  * If you don't already have an account: Register. Create a new account. Record your login details (email address/password)
  * Login
  * Open Settings page. https://transferwise.com/user/settings
  * Add new token: Full access
  * Add new token: Read only
  * Logout

### 2. Web Server
* Login to your Web server
* Create a new folder that can be accessed via a URL. (e.g.) xxx/public_html/TransferWise
* Create a subfolder called *includes*. (e.g.) xxx/public_html/TransferWise/includes
* Create and save [includes/configure.php](code/includes/configure.php) in the includes folder. Copy and paste the 4 tokens created at TransferWise before saving
* Create and save [includes/class_TransferWise.php](code/includes/class_TransferWise.php) in the includes folder 
* Create and save [test.php](code/test.php) in the main folder 

### 3. Your Web Browser
Using your favourite web browser, visit your test.php page with a URL similar to this: 
`https://my.web.server/TransferWise/test.php?SANDBOX&UNKNOWN`

You should see something like this (xxxxx will profile IDs):
```
TransferWise Server: Sandbox
Profile: UNKNOWN

get Profiles
Please edit includes/configure.php to include these lines

define('SANDBOX_ID_PERSONAL','xxxxx')
define('SANDBOX_ID_BUSINESS','xxxxx')
```
On your web server, edit your `includes/configure.php` file as instructed.

Visit your test.php page with a URL similar to this: 
`https://my.web.server/TransferWise/test.php?UNKNOWN`

You should see something like this:
```
TransferWise Server: Production
Profile: UNKNOWN

get Profiles
See resultPlease edit includes/configure.php to include these lines
define('PROFILE_ID_PERSONAL','xxxxx')
define('PROFILE_ID_BUSINESS','xxxxx')
```
On your web server, edit your `includes/configure.php` file as instructed.

Visit your test.php page with a URL similar to this: 
`https://my.web.server/TransferWise/test.php?SANDBOX`

You should now see something like this:
```
TransferWise Server: Sandbox
Profile: PERSONAL

Get Exch Rate
See result

Create an Address
See result

Create email Recipient
See result

Create GBP (sort_code) Recipient
See: https://api-docs.transferwise.com/#recipient-accounts-create-gbp-recipient
See result

Create GBP (IBAN) Recipient
See: https://api-docs.transferwise.com/#recipient-accounts-create-gbp-recipient
See result

Create USD Recipient
See: https://api-docs.transferwise.com/#recipient-accounts-create-usd-recipient
See result

Created account named Dummy Name, with id = 13708891
Deleting account with id = 13708891 ......
```

Click on the *See result* icons to expand the response returned by TransferWise. You should see that each succeeded.

Visit your test.php page with a URL similar to this: 
`https://my.web.server/TransferWise/test.php`

You should now see something like this:
```
TransferWise Server: Production
Profile: PERSONAL

Get Exch Rate
See result

Create an Address
See result

Create email Recipient
See result

Create GBP (sort_code) Recipient
See: https://api-docs.transferwise.com/#recipient-accounts-create-gbp-recipient
See result

Create GBP (IBAN) Recipient
See: https://api-docs.transferwise.com/#recipient-accounts-create-gbp-recipient
See result

Create USD Recipient
See: https://api-docs.transferwise.com/#recipient-accounts-create-usd-recipient
See result

Created account named Dummy Name, with id =
Deleting account with id = ......
stdClass Object
(
    [timestamp] => 2019-10-17T07:58:30.514570Z
    [errors] => Array
        (
            [0] => stdClass Object
                (
                    [code] => INVALID_INPUT
                    [message] => Unable to process request
                    [arguments] => Array
                        (
                        )
                )
        )
)
```

Note that it is normal to see some errors. The `test.php` file was trying to create real payment accounts with fake details, so they failed. If you edit `test.php` with valid account details, it should work.
