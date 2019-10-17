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
* Create and save this file in the includes folder. Copy and paste the 4 tokens created at TransferWise before saving
```
<?php
//
// Filename: .../TransferWise/includes/configure.php
//

//PRODUCTION
define('API_KEY_TOKEN_READONLY'    ,'copy_and_paste_from_TransferWise'); 
define('API_KEY_TOKEN_FULL_ACCESS' ,'copy_and_paste_from_TransferWise'); 
define('PROFILE_ID_PERSONAL'       ,'');
define('PROFILE_ID_BUSINESS'       ,'');

//SANDBOX
define('SANDBOX_TOKEN_READONLY'    ,'copy_and_paste_from_TransferWise'); 
define('SANDBOX_TOKEN_FULL_ACCESS' ,'copy_and_paste_from_TransferWise'); 
define('SANDBOX_ID_PERSONAL'       ,'');
define('SANDBOX_ID_BUSINESS'       ,'');
?>
```
* Create and save this file in the includes folder 
```
<?php
//
// Filename: .../includes/class_TransferWise.php
//
include 'includes/configure.php';

class TransferWise {
    // property declaration
    private $tw; 
    
    // method declarations
    public function __construct($profileId, $readOnly=true) {
        $this->tw = new stdClass();
        $this->tw->profileId = $profileId;
        switch($profileId){
            case PROFILE_ID_UNKNOWN: 
            case PROFILE_ID_PERSONAL: 
            case PROFILE_ID_BUSINESS: 
                $this->tw->api_key =  ($readOnly?API_KEY_TOKEN_READONLY:API_KEY_TOKEN_FULL); 
                $this->tw->url     = 'https://api.transferwise.com';
                break;
                
            case SANDBOX_ID_UNKNOWN: 
            case SANDBOX_ID_PERSONAL: 
            case SANDBOX_ID_BUSINESS: 
                
                $this->tw->api_key =  ($readOnly?SANDBOX_TOKEN_READONLY:SANDBOX_TOKEN_FULL); 
                $this->tw->url     = 'https://api.sandbox.transferwise.tech';
                break;
                
            default:
                 echo "Error: Unknown  profileId: $profileId \n";
        }
        
    }

    public function __destruct() {
        ;
    }
    
    public function getProfileId() {
       return $this->tw->profileId;
    }
    
    public function getProfiles(){
        return $this->GET('profiles');
    }

    public function getTransferById($transferId){
        return $this->GET("transfers/$transferId");
    }
    
    public function getExchangeRate($source,$target){
        return $this->GET("rates?source=$source&target=$target");
    } 
    
    public function postCreateAddress($country, $firstLine, $postCode, $city, $state=NULL, $occupation=NULL){
        $data = new stdClass();
        $data->profile = $this->tw->profileId;
        $data->details->country   = $country;
        $data->details->firstLine = $firstLine;
        $data->details->postCode  = $postCode;
        $data->details->city      = $city;
        if($state)     $data->details->state = $state;
        if($occupation)$data->details->occupation = $occupation;
        
        return $this->POST('addresses',$data);
    }
    
    public function postCreateAccount($accountHolderName, $currency, $type, $details, $ownedByCustomer=false){
        $data = new stdClass();
        $data->profile           = $this->tw->profileId;
        $data->accountHolderName = $accountHolderName;
        $data->currency          = $currency;
        $data->type              = $type;
        $data->ownedByCustomer   = $ownedByCustomer;
        $data->details           = $details;
        
        return $this->POST('accounts',$data);
     }
     
    public function deleteAccount($accountId){
        return $this->DELETE("accounts/$accountId");
    }
    
    private function POST($url,$data){
        return $this->curl('POST',$url,$data);
    }

    private function GET($url){
        return $this->curl('GET',$url);
    }
    
    private function DELETE($url){
        return $this->curl('DELETE',$url);
    }
    
    private function PUT($url){
        return $this->curl('PUT',$url);
    }

    private function curl($mode, $curl_url,$data=NULL){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_URL, $this->tw->url."/v1/$curl_url");
        $headerArray[] = "Authorization: Bearer ".$this->tw->api_key;
        if($mode=='POST'){
            $payload = json_encode($data);
            $headerArray[] = "Content-Type: application/json";
            $headerArray[] = 'Content-Length: ' . strlen($payload);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $mode); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        
        $response = curl_exec($ch);
        if($response === false){
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close ($ch);
        return  $response;
    }
}
?>
```
* Create and save this file in the main folder 
```
<?php
//
// Filename: .../test.php
//
?>
<!DOCTYPE html>
<head>
  <title>TransferWise Test</title>
</head>
<body>
<?

include('includes/class_TransferWise.php');

//Set profileID
// Uncomment only one of the lines below at a time
$profileId = 
    //Phase 1 (IDs unknown) 
    SANDBOX_ID_UNKNOWN  
//  PROFILE_ID_UNKNOWN  
    
    //Phase 2 (IDs known)
//  SANDBOX_ID_PERSONAL 
//  SANDBOX_ID_BUSINESS
//  PROFILE_ID_PERSONAL
//  PROFILE_ID_BUSINESS
    
    ;
    

//Create Read Only instance
$tw = new TransferWise($profileId);

if(strstr($profileId,'_UNKNOWN') !== false) {
    //Phase 1 - IDs unknown
    $profiles=json_decode($tw->getProfiles());
    echo "<hr>Get Profiles<br>";
    echo '<details><summary>See result</summary>';
    echo '<pre>'.print_r($profiles,1).'</pre>';
    echo '</details>';
    
    $profilePrefix = strtok($profileId,'_');
    echo "Please edit includes/configure.php to include these lines\n\n"; 
    echo '<pre>';
    foreach($profiles as $profile){
      echo "define('$profilePrefix".'_ID_'.strtoupper($profile->type)."','$profile->id')\n"; 
    }
    echo '</pre>';
    exit;
}

echo "<hr>Get Exch Rate<br>";
echo '<details><summary>See result</summary>';
echo '<pre>';
echo print_r(json_decode($tw->getExchangeRate('USD','EUR')),1);
echo '</pre>';
echo '</details>';
unset ($tw);

//Create Full Access instance
$tw = new TransferWise($profileId, false);

echo "<hr>Create an Address<br>";
echo '<details><summary>See result</summary>';
echo '<pre>';
echo print_r(json_decode($tw->postCreateAddress('GB', 'Fred', 'E16JJ', 'London', '')),1);
echo '</pre>';
echo '</details>';


echo "<hr>Create email Recipient<br>";
$details = new stdClass();
$details->email = 'jean@boggs.com';
echo '<details><summary>See result</summary>';
echo '<pre>';
echo print_r(json_decode($tw->postCreateAccount('Jean Bloggs', 'GBP', 'email', $details)),1);
echo '</pre>';
echo '</details>';

// Create payment recipients
//  Each currency has different requirements for creating a payment recipient.
//  See https://api-docs.transferwise.com/#recipient-accounts-create-xxx-recipient
//   where xxx = 3 character currency (e.g. USD, GBP, ...)

$url = 'https://api-docs.transferwise.com/#recipient-accounts-create-gbp-recipient';
echo "<hr>Create GBP (sort_code) Recipient<br>";
echo "See: <a href=\"$url\">$url</a><br>";
$details = new stdClass();
$details->legalType     = 'PRIVATE';
$details->sortCode      = '40-30-20';
$details->accountNumber = '12345678';
echo '<details><summary>See result</summary>';
echo '<pre>';
echo print_r(json_decode($tw->postCreateAccount('Jean Bloggs', 'GBP', 'sort_code', $details)),1);
echo '</pre>';
echo '</details>';

$url = 'https://api-docs.transferwise.com/#recipient-accounts-create-gbp-recipient';
echo "<hr>Create GBP (IBAN) Recipient<br>";
echo "See: <a href=\"$url\">$url</a><br>";
$details = new stdClass();
$details->legalType = 'PRIVATE';
$details->IBAN      = 'GB33BUKB20201555555555';
echo '<details><summary>See result</summary>';
echo '<pre>';
echo print_r(json_decode($tw->postCreateAccount('Jean Bloggs', 'GBP', 'iban', $details)),1);
echo '</pre>';
echo '</details>';

$url = 'https://api-docs.transferwise.com/#recipient-accounts-create-usd-recipient';
echo "<hr>Create USD Recipient<br>";
echo "See: <a href=\"$url\">$url</a><br>";
$details = new stdClass();
$details->legalType     = 'PRIVATE';
$details->abartn        = '111000025';
$details->accountNumber = '12345678';
$details->accountType   = 'CHECKING';
$details->address->country   = 'GB';
$details->address->city      = 'London';
$details->address->postCode  = '10025';
$details->address->firstLine = '50 Branson Ave';
echo '<details><summary>See result</summary>';
echo '<pre>';
echo print_r(json_decode($tw->postCreateAccount('Jean Bloggs', 'USD', 'aba', $details)),1);
echo '</pre>';
echo '</details>';
// Check DELETE works
//  1. Create an account
//  2. Delete same

$details = new stdClass();
$details->legalType     = 'PRIVATE';
$details->abartn        = '111000025';
$details->accountNumber = '12345678';
$details->accountType   = 'CHECKING';
$details->address->country   = 'GB';
$details->address->city      = 'London';
$details->address->postCode  = '10025';
$details->address->firstLine = '50 Branson Ave';

$response = json_decode($tw->postCreateAccount('Dummy Name', 'USD', 'aba', $details));
$accountId = $response->id;
echo "<hr>Created account named Dummy Name, with id = $accountId";
echo "<br>Deleting account with id = $accountId ......<br>";
echo '<pre>';
echo print_r(json_decode($tw->deleteAccount($accountId)),1);
echo '</pre>';

?>
</body>
</html>      
```
