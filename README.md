# TransferWise Simple API PHP Class

## Introduction
This PHP class is completely standalone; it does not require composer to bring in other code. As such this is a light-weight module for easy inclusion into bigger projects.

It has been written for my own use, and as such does not include methods for all TransferWise [API calls](https://api-docs.transferwise.com/#transferwise-api), but I welcome contributors to add methods to add access to other API calls.

A *test* program is included to :
* Confirm the class has been configured correctly, and is working OK;
* Show example code on how to use the class

## Requirements
A webserver with PHP, that you create/save new PHP files.

## Security
NEVER save your API keys in the main code files. In this tutorial, they are saved in a separate *include* file. 

For added security, you can ignore all the non-SANDBOX instructions below and restrict your actions to the (safe) Sandbox environment.

## Summary of Steps

1. Strong Customer Authentication (SCA) keys
    1. Create sandbox sandbox_private.pem and sandbox_public.pem files
    1. Create production private.pem and public.pem files
1. Transfer Wise
    1. Create Sandbox account (Personal and Business)
        1. Create API Token - Read only
        1. Create API Token - Full access
        1. Upload sandbox_public.pem file
    1. Create Production account (Personal, if not already created)
        1. Create API Token - Read only
        1. Create API Token - Full access
        1. Upload public.pem file
1. Your Web Server
    1. Create configure.php. Enter the API Tokens.
    1. Create class_TransferWise.php
    1. Create test.php
    1. Create private/public pem files
1. Your Web Browser
    1. Run test.php to get profileIDs
    1. Update configure.php with these profileIDs
    1. Run test.php to exercise the code in these modes:
       1. SANDBOX
       1. PRODUCTION (live)

## Step by Step
### 1. Strong Customer Authentication (SCA) keys
Details on SCA are here: https://api-docs.transferwise.com/#payouts-guide-strong-customer-authentication

Two methods are given here to create your pairs of private/public pem files.
1. Method 1 - Create pem files online
    1. Visit: https://travistidwell.com/jsencrypt/demo/
    1. Disconnect your computer from all networks.  (The web page above does not need the Internet. This is just an extra security measure.)
    1. Select Key Size = 2048
    1. Click Generate New Keys
    1. Create file on local PC called sandbox_public.pem
    1. Copy and Paste all the Private Key text into sandbox_public.pem. Save.
    1. Repeat steps above for files private.pem and public.pem
    1. Reconnect your computer to the network
    
1. Method 2 - Create pem files from Linux command line
    1. At the command line of a linux computer enter these commands
    ```
    $ openssl genrsa -out sandbox_private.pem 2048
    $ openssl rsa -pubout -in sandbox_private.pem -out sandbox_public.pem
    
    $ openssl genrsa -out private.pem 2048
    $ openssl rsa -pubout -in private.pem -out public.pem
    ```
    1. Download the 4 files to your local PC

### 1. Transfer Wise
* Visit https://sandbox.transferwise.tech/
  * Register. Create a new account. Record your login details (email address/password)
  * Open Settings page. https://sandbox.transferwise.tech/user/settings
  * Add new API token: Full access
  * Add new API token: Read only
  * Click *Manage public keys*  ->  *Add new key*  ->  *Continue*
  * Enter security code (i.e. 111111)  ->  *Done*
  * Enter name: *Sandbox Public Key*
  * Select your sandbox_public.pem file  ->  *Add key*
  * Click *Turn on SCA* (This option will be On in all cases after 1-Oct-2020)
  * Create a Business account. (This shares the same API tokens and Public key already created) 
  * Logout

* Visit https://transferwise.com
  * If you don't already have an account: Register. Create a new account. Record your login details (email address/password)
  * Login
  * Open Settings page. https://transferwise.com/user/settings
  * Add new API token: Full access
  * Add new API token: Read only
  * Click *Manage public keys*  ->  *Add new key*  ->  *Continue*
  * Enter security code  ->  *Done*
  * Enter name: *Public Key*
  * Select your public.pem file  ->  *Add key*
  * Click *Turn on SCA* (This option will be On in all cases after 1-Oct-2020)
  * Logout

### 2. Web Server
* Login to your Web server
* Create a new folder that can be accessed via a URL. (e.g.) xxx/public_html/TransferWise
* Create a subfolder called *includes*. (e.g.) xxx/public_html/TransferWise/includes
* Create and save [includes/configure.php](code/includes/configure.php) in the includes folder. Copy and paste the 4 tokens created at TransferWise before saving.
* Create and save sandbox_private.pem in the includes folder
* Create and save private.pem in the includes folder
* Create and save [class_TransferWise.php](code/includes/class_TransferWise.php) in the includes folder 
* Create and save [test.php](code/test.php) in the main folder 
* Restrict the permissions of the private.pem file to 0400  (= User Read only)
* Restrict the permissions of the includes folder to 0555  
* Restrict the permissions of the main folder to 0555  


### 3. Your Web Browser
Using your favourite web browser, visit your test.php page with a URL similar to this: 
`https://my.web.server/TransferWise/test.php?SANDBOX&UNKNOWN`

You should see something like this (xxxxx will profile IDs):
```
TransferWise Server: Sandbox
Profile: UNKNOWN

get Profiles
See result
Please edit includes/configure.php to include these lines

define('SANDBOX_ID_PERSONAL','xxxxx');
define('SANDBOX_ID_BUSINESS','xxxxx');
```
On your web server, edit your `includes/configure.php` file as instructed.

Visit your test.php page with a URL similar to this: 
`https://my.web.server/TransferWise/test.php?UNKNOWN`

You should see something like this:
```
TransferWise Server: Production
Profile: UNKNOWN

get Profiles
See result
Please edit includes/configure.php to include these lines
define('PROFILE_ID_PERSONAL','xxxxx');
define('PROFILE_ID_BUSINESS','xxxxx');
```
Note: If you do not have a Business account in your live TransferWise profile, you will only see the PROFILE_ID_PERSONAL line.

On your web server, edit your `includes/configure.php` file as instructed.

Visit your test.php page with a URL similar to this: 
`https://my.web.server/TransferWise/test.php?SANDBOX`

You should now see something like this:
```
TransferWise Server: Sandbox
Profile: PERSONAL

get Acct Balance AUD
See result

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

Transfer Funds: Step 1 - Create Quote
See result

Transfer Funds: Step 2 - Create Recipient
See result

Transfer Funds: Step 3 - Create Transfer
See result

Transfer Funds: Step 4 - Fund Transfer
See result

Deleting account with id =  ......
See result
```

Click on the *See result* icons to expand the response returned by TransferWise. You should see that each succeeded.

Visit your test.php page with a URL similar to this: 
`https://my.web.server/TransferWise/test.php`

You should now see something like this:
```
TransferWise Server: Production
Profile: PERSONAL

get Acct Balance AUD
See result

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

Transfer Funds: Step 1 - Create Quote
See result

Transfer Funds: Step 2 - Create Recipient
See result

Transfer Funds: Step 3 - Create Transfer
See result

Transfer Funds: Step 4 - Fund Transfer
See result

Deleting account with id =  ......
See result
```

Note that it is normal to see some errors. The `test.php` file was trying to create real payment accounts with fake details, so they failed. If you edit `test.php` with valid account details, it should work.
