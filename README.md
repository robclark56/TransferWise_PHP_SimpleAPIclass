# TransferWise Simple API PHP Class

## Introduction
This PHP class is completely standalone; it does not require composer to bring in other code. As such this is a light-weight module for easy inclusion into bigger projects.

It has been written for my own use, and as such does not include methods for all TransferWise [API calls](https://api-docs.transferwise.com/#transferwise-api), but I welcome contributors to add methods to add access to other API calls.

## Requirements
A webserver with PHP, that you cerate/save new PHP files.

## Security
NEVER save your API keys in the main code files. In this tutorial, they are saved in a separate *include* file. The API key in this tutorial is a *Limited Access* key; meaning if it is compromised the bad actor can not spend your money. You will need to use a *Full Access* API key if you need to spend money, etc.



