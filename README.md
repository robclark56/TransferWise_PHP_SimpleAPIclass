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
    1. Run test.php to get profileIds
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
  * Open Settings page. https://transferwise.com/user/settings
  * Add new token: Full access
  * Add new token: Read only
  * Logout

### 2. Web Server
* Login to your Web server
* Create a new folder that can be accessed via a URL. (e.g.) xxx/public_html/TransferWise
* Create a subfolder called *includes*. (e.g.) xxx/public_html/TransferWise/includes


