<?php
//
// Filename: .../includes/class_TransferWise.php
//
include 'includes/configure.php';       //Edit this file with your API KEYs, and ProfileIDs (when known)

class TransferWise {
    // property declaration
    private $tw; 
    
    // method declarations
    public function __construct(
            $profileId,                 //a valid ProfileID, or PROFILE_ID_UNKNOWN if not known
            $readOnly=true              //true (default): use the Read Only Token. false: use the full access token
            ) {
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
        return $this->GET('/v1/profiles');
    }

    public function getTransferById(
            $transferId                 //a valid transferId
            ){
        return $this->GET("/v1/transfers/$transferId");
    }
    
    public function getExchangeRate(
            $source,                    //a 3-char currency code. e.g. 'USD'
            $target                     //a 3-char currency code. e.g. 'EUR'
            ){
        return $this->GET("/v1/rates?source=$source&target=$target");
    } 
    
    public function getAccountBalance(
            $currency=null              //a 3-char currency code. e.g. 'EUR'. If null, then return account balances for all currencies
            ){
        $json = $this->GET("/v1/borderless-accounts?profileId=".$this->tw->profileId);
        if(!$currency) return $json;
        $accounts = json_decode($json);
        $details=$accounts[0];
        if(!$details) return "Error: getAccountBalance() No Details returned: $json";
        foreach($details->balances as $balance){
            if($balance->currency == $currency) 
              return json_encode($balance);
        }
    }
    
    public function getStatement(
            $currency,                  //a 3-char currency code. e.g. 'EUR'.
            $type='json',               //'json' (default), 'pdf', or 'csv'
            $intervalStart=null,        //starting time of statement. Default: 1 month ago
            $intervalEnd=null           //ending   time of statement. Default: now
            ){
        //Defaults
        if(!$intervalStart){
            //1 month ago
            $intervalStart = gmdate("Y-m-d\TH:i:s\Z", strtotime('-1 month'));
        }
        if(!$intervalEnd){
            //now
            $intervalEnd = gmdate("Y-m-d\TH:i:s\Z");
        }
        
        //get borderlessAccountId
        $accountBalances = json_decode($this->getAccountBalance());
        $borderlessAccountId = $accountBalances[0]->id;
        
        //get statement
        return $this->GET('/v3/profiles/'.$this->tw->profileId."/borderless-accounts/$borderlessAccountId/statement.$type?currency=$currency&intervalStart=$intervalStart&intervalEnd=$intervalEnd");
    }
    
    public function getRecipientAccounts(
            $profileId=null,        //[optional] Personal or business profile id  
            $currency=null          //[optional] a 3-char currency code. e.g. 'EUR'.
            ){
        $profileId && $data[] = array('profileId' => $profileId);
        $currency  && $data[] = array('currency' => $currency);
        $data && ($args='?'.http_build_query($data));
        return $this->GET('/v1/accounts'.$args);
    }
    
    public function postCreateAddress(
            $country,               //see https://api-docs.transferwise.com/#addresses-create
            $firstLine, 
            $postCode, 
            $city, 
            $state=NULL, 
            $occupation=NULL
            ){
        $data = new stdClass();
        $data->profile = $this->tw->profileId;
        $data->details = new stdClass();
        $data->details->country   = $country;
        $data->details->firstLine = $firstLine;
        $data->details->postCode  = $postCode;
        $data->details->city      = $city;
        if($state)     $data->details->state = $state;
        if($occupation)$data->details->occupation = $occupation;
        
        return $this->POST('/v1/addresses',$data);
    }
    
    public function postCreateAccount(
            $accountHolderName,     //Name (string)
            $currency,              //a 3-char currency code. e.g. 'EUR'.
            $type,                  //depends on curreny. See //see https://api-docs.transferwise.com/#recipient-accounts-create
            $details,               //see https://api-docs.transferwise.com/#recipient-accounts-create
            $ownedByCustomer=false  //true = you own this acct. false = you don't own this acct
            ){
        $data = new stdClass();
        $data->profile           = $this->tw->profileId;
        $data->accountHolderName = $accountHolderName;
        $data->currency          = $currency;
        $data->type              = $type;
        $data->ownedByCustomer   = $ownedByCustomer;
        $data->details           = $details;
        
        return $this->POST('/v1/accounts',$data);
    }
    
    public function postCreateQuote(
            $type,               	//'BALANCE_PAYOUT' for payments or 'BALANCE_CONVERSION' for conversion between balances
            $sourceCurrency,        //a 3-char currency code. e.g. 'EUR'.
            $targetCurrency,        //a 3-char currency code. e.g. 'EUR'.
            $sourceAmount=null,     //Amount in source currency. If specified, $targetAmount must be null.
            $targetAmount=null      //Amount in target currency. If specified, $sourceAmount must be null. 
            ){
        $data = new stdClass();
        $data->profile           = $this->tw->profileId;
        $data->target            = $targetCurrency;
        $data->source            = $sourceCurrency;
        $data->rateType          = 'FIXED';
        if($targetAmount) $data->targetAmount = $targetAmount;
        else              $data->sourceAmount = $sourceAmount;
        $data->type              = $type;
       
        return $this->POST('/v1/quotes',$data);
    }
    
    public function postCreateTransfer(
            $targetAccount,         //recipient account id 
            $quoteId,               //quote id
            $reference,             //Recipient will see this reference text in their bank statement
            $transferPurpose =null, //[Conditional] see: https://api-docs.transferwise.com/#transfers-requirements
            $sourceOfFunds =null    //[Conditional]see: https://api-docs.transferwise.com/#transfers-requirements
            ){
        $data = new stdClass();
        $data->targetAccount            = $targetAccount;
        $data->quote                    = $quoteId;
        $data->customerTransactionId    = $this->createUUID();
        $data->details =  new stdClass();
        $data->details->reference       = $reference;
        $transferPurpose && $data->details->transferPurpose = $transferPurpose;
        $sourceOfFunds && $data->details->sourceOfFunds = $sourceOfFunds;
    
        return $this->POST('/v1/transfers',$data);
    }
    
    public function postFundTransfer(
            $transferId             //transferID from postCreateTransfer()
            ){
        $data = new stdClass();
        $data->type     = 'BALANCE';
        
        return $this->POST("/v3/profiles/".$this->tw->profileId."/transfers/$transferId/payments",$data);
    }
    
    public function postProfileWebhookCreate(
            $name,                  //any nickname
            $trigger_on,            //'transfers#state-change', 'transfers#active-cases', or 'balances#credit'  
            $url                    //the URL where your server will be listening for events
            ){
        $data = new stdClass();
        $data->name                = $name;
        $data->trigger_on          = $trigger_on;
        $data->delivery            = new stdClass();
        $data->delivery->version   = '2.0.0';
        $data->delivery->url       = $url;
         
        return $this->POST('/v3/profiles/'.$this->tw->profileId.'/subscriptions',$data);
    }
    
    public function getProfileWebhookList(){
        return $this->GET('/v3/profiles/'.$this->tw->profileId.'/subscriptions');
    }

    public function deleteProfileWebhook(
            $id             //id of ProfileWebhook to delete, as returned by getProfileWebhookList()
            ){
        return $this->DELETE('/v3/profiles/'.$this->tw->profileId."/subscriptions/$id");
    }

    public function deleteAccount(
            $accountId      //id of account to delete.
            ){
        return $this->DELETE("/v1/accounts/$accountId");
    }
    
    
//////////////// Internal (private) worker functions ////////////////////////////

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
        curl_setopt($ch, CURLOPT_URL, $this->tw->url."$curl_url");
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
