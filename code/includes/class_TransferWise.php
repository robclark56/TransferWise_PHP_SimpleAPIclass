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
