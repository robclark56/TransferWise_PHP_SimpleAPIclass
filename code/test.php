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
 //   SANDBOX_ID_UNKNOWN  
//   PROFILE_ID_UNKNOWN  
    
    //Phase 2 (IDs known)
   SANDBOX_ID_PERSONAL 
//    SANDBOX_ID_BUSINESS
//    PROFILE_ID_PERSONAL
//    PROFILE_ID_BUSINESS
    
    ;
    

//Create Read Only instance
$tw = new TransferWise($profileId);

$profiles=json_decode($tw->getProfiles());
//echo '<hr>get Profiles <br><pre>'.print_r($profiles,1).'<br>';

if(strstr($profileId,'_UNKNOWN') !== false) {
    //Phase 1 - IDs unknown
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
echo '<details>';
echo '<summary>See result</summary>';
echo '<pre>';
echo print_r(json_decode($tw->getExchangeRate('USD','EUR')),1);
echo '</pre>';
echo '</details>';
unset ($tw);

//Create Full Access instance
$tw = new TransferWise($profileId, false);

echo "<hr>Create an Address<br>";
echo '<details>';
echo '<summary>See result</summary>';
echo '<pre>';
echo print_r(json_decode($tw->postCreateAddress('GB', 'Fred', 'E16JJ', 'London', '')),1);
echo '</pre>';
echo '</details>';


echo "<hr>Create email Recipient<br>";
$details = new stdClass();
$details->email = 'jean@boggs.com';
echo '<details>';
echo '<summary>See result</summary>';
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
echo '<details>';
echo '<summary>See result</summary>';
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
echo '<details>';
echo '<summary>See result</summary>';
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
echo '<details>';
echo '<summary>See result</summary>';
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