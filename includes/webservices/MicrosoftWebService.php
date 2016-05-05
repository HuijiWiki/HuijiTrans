<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */
 
/**
 * Implements support for Microsoft translation api v2.
 * @see http://msdn.microsoft.com/en-us/library/ff512421.aspx
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class MicrosoftWebService extends TranslationWebService {
   public function getType() {
       return 'mt';
   }
 
   protected function mapCode( $code ) {
       $map = array(
           'zh-hant' => 'zh-CHT',
           'zh-hans' => 'zh-CHS',
           'zh' => 'zh-CHS',
           'zh-cn' => 'zh-CHS',
       );
 
       return isset( $map[$code] ) ? $map[$code] : $code;
   }
 
   protected function doPairs() {
    //Client ID of the application.
        $clientID       = "Huijiwiki";
        //Client Secret key of the application.
        $clientSecret = $this->config['key'];
        //OAuth Url.
        $authUrl      = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
        //Application Scope Url
        $scopeUrl     = "http://api.microsofttranslator.com";
        //Application grant type
        $grantType    = "client_credentials";
        //Create the AccessTokenAuthentication object.
        $authObj      = new AccessTokenAuthentication();
        //Get the Access token.
        $accessToken  = $authObj->getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl);
        //Create the authorization Header string.
        $appId = "Bearer ". $accessToken;
       if ( !isset( $this->config['key'] ) ) {
           throw new TranslationWebServiceException( 'API key is not set' );
       }
 
       $options = array();
       $options['method'] = 'GET';
       $options['timeout'] = $this->config['timeout'];
 
       $params = array(
           'appId' => $appId,
       );
 
       $url = 'http://api.microsofttranslator.com/V2/Http.svc/GetLanguagesForTranslate?';
       $url .= wfArrayToCgi( $params );
 
       $req = MWHttpRequest::factory( $url, $options );
       $status = $req->execute();
 
       if ( !$status->isOK() ) {
           $error = $req->getContent();
           // Most likely a timeout or other general error
           $exception = 'Http request failed:' . serialize( $error ) . serialize( $status );
           throw new TranslationWebServiceException( $exception );
       }
 
       $xml = simplexml_load_string( $req->getContent() );
 
       $languages = array();
       foreach ( $xml->string as $language ) {
           $languages[] = (string)$language;
       }
 
       // Let's make a cartesian product, assuming we can translate from any
       // language to any language
       $pairs = array();
       foreach ( $languages as $from ) {
           foreach ( $languages as $to ) {
               $pairs[$from][$to] = true;
           }
       }
       $pairs['zh']['en'] = true;
       $pairs['en']['zh'] = true;
       $pairs['en']['zh-hans'] = true;
       $pairs['en']['zh-cn'] = true;
       $pairs['en']['zh-hant'] = true;
       $pairs['en']['zh-CHS'] = true;
       $pairs['en']['zh-CHT'] = true;
       $pairs['zh-CHS']['en'] = true;
       $pairs['zh-CHT']['en'] = true;
       return $pairs;
   }
 
   protected function getQuery( $text, $from, $to ) {
       if ( !isset( $this->config['key'] ) ) {
           throw new TranslationWebServiceException( 'API key is not set' );
       }
        $clientID       = "Huijiwiki";
        //Client Secret key of the application.
        $clientSecret = $this->config['key'];
        //OAuth Url.
        $authUrl      = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
        //Application Scope Url
        $scopeUrl     = "http://api.microsofttranslator.com";
        //Application grant type
        $grantType    = "client_credentials";
        //Create the AccessTokenAuthentication object.
        $authObj      = new AccessTokenAuthentication();
        //Get the Access token.
        $accessToken  = $authObj->getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl);
        //Create the authorization Header string.
        $appId = "Bearer ". $accessToken;
 
       $text = trim( $text );
       $text = $this->wrapUntranslatable( $text );
 
       $params = array(
           'text' => $text,
           'from' => $from,
           'to' => $to,
           'appId' => $appId,
       );
 
       return TranslationQuery::factory( $this->config['url'] )
           ->timeout( $this->config['timeout'] )
           ->queryParamaters( $params );
   }
 
   protected function parseResponse( TranslationQueryResponse $reply ) {
       $body = $reply->getBody();
 
       $text = preg_replace( '~<string.*>(.*)</string>~', '\\1', $body );
       $text = Sanitizer::decodeCharReferences( $text );
       $text = $this->unwrapUntranslatable( $text );
 
       return $text;
   }
}
class AccessTokenAuthentication {
    /*
     * Get the access token.
     *
     * @param string $grantType    Grant type.
     * @param string $scopeUrl     Application Scope URL.
     * @param string $clientID     Application client ID.
     * @param string $clientSecret Application client ID.
     * @param string $authUrl      Oauth Url.
     *
     * @return string.
     */
                                    function getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl){
                                    try {
            //Initialize the Curl Session.
            $ch = curl_init();
            //Create the request Array.
            $paramArr = array (
                                    'grant_type'    => $grantType,
                 'scope'         => $scopeUrl,
                 'client_id'     => $clientID,
                 'client_secret' => $clientSecret
            );
            //Create an Http Query.//
            $paramArr = http_build_query($paramArr);
            //Set the Curl URL.
            curl_setopt($ch, CURLOPT_URL, $authUrl);
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArr);
            //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Execute the  cURL session.
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if($curlErrno){
                $curlError = curl_error($ch);
                throw new Exception($curlError);
            }
            //Close the Curl Session.
            curl_close($ch);
            //Decode the returned JSON string.

            if($strResponse == false) throw new Exception("curl Error");
            $objResponse = json_decode($strResponse);
	    
            if ($objResponse->error){
                throw new Exception($objResponse->error_description);
            }
	    

            return $objResponse->access_token;
        } catch (Exception $e) {
            echo "Exception-".$e->getMessage();
        }
    }
}
