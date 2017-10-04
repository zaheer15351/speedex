<?php

namespace Speedex\Shipment\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
 
class Data extends AbstractHelper
{
	
	public function getSpeedexSessionId() { 
       	/*$logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('getSpeedexSessionId');*/
        $this->writeLogs("getSpeedexSessionId");
		$username = $this->getSpeedexConfigData("username");
		$password = $this->getSpeedexConfigData("password");
		if($username=="" || $password=="") {
			$this->throwException("Speedex is not properly configured, please contact support.");
		}
		// $password = "d" ;
        $xml_post_string  = 
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:spe="http://www.speedex.gr/">
			<soapenv:Header/>
			<soapenv:Body>
				<spe:CreateSession>
				<spe:username>'.$username.'</spe:username>
				<spe:password>'.$password.'</spe:password>
				</spe:CreateSession>
			</soapenv:Body>
		</soapenv:Envelope>';

		$response = $this->executeApi($xml_post_string)->CreateSessionResponse;
		$returnCode = (string)$response->returnCode;
		$sessionId = (string)$response->sessionId;
		if($response->returnCode!="1"){
			// throw exception
			$this->throwException("An error has occured while processing with speedex, please contact support.");
		}

		return $sessionId;

	}
	public function createShipment($sessionId, $order)
	{
		/*$logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('createShipment');*/
        $this->writeLogs("createShipment");
       	
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$shippingAddress = $order->getShippingAddress();
		$paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
		$codAmount = ($paymentMethod=="cashondelivery") ? $order->getGrandTotal() : 0 ;
		// print_r($orderAmount);exit;
        $countryName = $objectManager->create('\Magento\Directory\Model\Country')->load($shippingAddress->getCountryId())->getName();
		// print_r($shippingAddress->getData());exit;
		$branchId = $this->getSpeedexConfigData("branchid");
		$customerId = $this->getSpeedexConfigData("customercode");
		$agreementId = $this->getSpeedexConfigData("agreementcode");
		if($branchId=="" || $customerId=="" || $agreementId=="") {
			$this->throwException("Speedex is not properly configured, please contact support.");
		}
		$RCV_Name = $shippingAddress->getFirstname()." ".$shippingAddress->getLastname();
		if($RCV_Name=="") {
			$this->throwException("Name is mandatory in shipping address.");
		}
		$RCV_Addr1 = $shippingAddress->getStreet()[0];
		if($RCV_Addr1=="") {
			$this->throwException("Address is mandatory in shipping address.");
		}
		$RCV_Zip_Code = $shippingAddress->getPostcode();
		if($RCV_Zip_Code=="") {
			$this->throwException("Postal is mandatory in shipping address.");
		}
		$RCV_City = $shippingAddress->getCity();
		if($RCV_City=="") {
			$this->throwException("City is mandatory in shipping address.");
		}
		$RCV_Country = $countryName;
		if($RCV_Country=="") {
			$this->throwException("Country is mandatory in shipping address.");
		}
		$RCV_Tel1 = $shippingAddress->getTelephone();
		if($RCV_Tel1=="") {
			$this->throwException("Telephone is mandatory in shipping address.");
		}
		$Voucher_Weight = $order->getWeight();
		$Pod_Amount_Cash = $codAmount ; // if payment is cod, the value will be total order amount
		$Pod_Amount_Description = "M"; // In case of COD:  M for Cash  E for Cheque 
		$Security_Value = "0" ;
		$Express_Delivery = "0" ;
		$Saturday_Delivery = "0" ;
		$Voucher_Volume = "0" ; //Shipment Volume. 
		$PayCode_Flag = "1" ; // It defines who will be charged for the shipment. 1 for Consignor 2 for Consignee 3 for other customer
		$Items = "1" ;// Total parcels per shipment. As an input it should contain the total number of parcels of the shipment. Web service will return as many BOL elements as the given Items number.
		$_cust_Flag = "0" ; // This field defines what consignor data will be printed on the BOL. A value of 0 indicates that the default (the information associated with the given Customer Id) Consignor Company, Name and Address will be used.
		$tableFlag = "3" ; // tableFlag (CreateBOLwithOrder operation parameter) should be equal to 3. 
		$xml_post_string  = 
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:spe="http://www.speedex.gr/">
		<soapenv:Header/>
		<soapenv:Body>
			<spe:CreateBOL>
				<spe:sessionID>'.$sessionId.'</spe:sessionID>
				<!--Optional:-->
				<spe:inListPod>
				<!--Zero or more repetitions:-->
					<spe:BOL>
					<!--Optional:-->
					<spe:voucher_code></spe:voucher_code>
					<!--Optional:-->
					<spe:EnterBranchId>'.$branchId.'</spe:EnterBranchId>
					<!--Optional:-->
					<spe:SND_Customer_Id>'.$customerId.'</spe:SND_Customer_Id>
					<!--Optional:-->
					<spe:Snd_agreement_id>'.$agreementId.'</spe:Snd_agreement_id>
					<!--Optional:-->
					<spe:RCV_Afm></spe:RCV_Afm>
					<!--Optional:-->
					<spe:RCV_DOY></spe:RCV_DOY>
					<!--Optional:-->
					<spe:RCV_Company></spe:RCV_Company>
					<!--Optional:-->
					<spe:RCV_Name>'.$RCV_Name.'</spe:RCV_Name>
					<!--Optional:-->
					<spe:RCV_Addr1>'.$RCV_Addr1.'</spe:RCV_Addr1>
					<!--Optional:-->
					<spe:RCV_Addr2></spe:RCV_Addr2>
					<!--Optional:-->
					<spe:RCV_Zip_Code>'.$RCV_Zip_Code.'</spe:RCV_Zip_Code>
					<!--Optional:-->
					<spe:RCV_City>'.$RCV_City.'</spe:RCV_City>
					<!--Optional:-->
					<spe:RCV_Country>'.$RCV_Country.'</spe:RCV_Country>
					<!--Optional:-->
					<spe:RCV_Tel1>'.$RCV_Tel1.'</spe:RCV_Tel1>
					<!--Optional:-->
					<spe:RCV_Tel2></spe:RCV_Tel2>
					<spe:Voucher_Weight>'.$Voucher_Weight.'</spe:Voucher_Weight>
					<spe:Pod_Amount_Cash>'.$Pod_Amount_Cash.'</spe:Pod_Amount_Cash>
					<spe:Security_Value>'.$Security_Value.'</spe:Security_Value>
					<spe:Express_Delivery>'.$Express_Delivery.'</spe:Express_Delivery>
					<spe:Saturday_Delivery>'.$Saturday_Delivery.'</spe:Saturday_Delivery>
					<!--Optional:-->
					<spe:Time_Limit></spe:Time_Limit>
					<!--Optional:-->
					<spe:Comments_2853_1></spe:Comments_2853_1>
					<!--Optional:-->
					<spe:Comments_2853_2></spe:Comments_2853_2>
					<!--Optional:-->
					<spe:Comments_2853_3></spe:Comments_2853_3>
					<!--Optional:-->
					<spe:Comments></spe:Comments>
					<spe:Voucher_Volume>'.$Voucher_Volume.'</spe:Voucher_Volume>
					<!--Optional:-->
					<spe:Pod_Amount_Description>'.$Pod_Amount_Description.'</spe:Pod_Amount_Description>
					<spe:PayCode_Flag>'.$PayCode_Flag.'</spe:PayCode_Flag>
					<!--Optional:-->
					<spe:BranchBankCode></spe:BranchBankCode>
					<!--Optional:-->
					<spe:Paratiriseis_2853_1></spe:Paratiriseis_2853_1>
					<!--Optional:-->
					<spe:Paratiriseis_2853_2></spe:Paratiriseis_2853_2>
					<!--Optional:-->
					<spe:Paratiriseis_2853_3></spe:Paratiriseis_2853_3>
					<!--Optional:-->
					<spe:email></spe:email>
					<!--Optional:-->
					<spe:BasicService></spe:BasicService>
					<spe:Items>'.$Items.'</spe:Items>
					<!--Optional:-->
					<spe:Vouc_descr></spe:Vouc_descr>
					<!--Optional:-->
					<spe:Comments_new></spe:Comments_new>
					<spe:_cust_Flag>'.$_cust_Flag.'</spe:_cust_Flag>
					</spe:BOL>
				</spe:inListPod>
				<spe:tableFlag>'.$tableFlag.'</spe:tableFlag>
			</spe:CreateBOL>
		</soapenv:Body>
		</soapenv:Envelope>';
		$response = $this->executeApi($xml_post_string)->CreateBOLResponse;
		$returnCode = (string)$response->returnCode;
		$voucherId = (string)$response->outListPod->BOL->voucher_code;
		if($response->returnCode!="1"){
			// throw exception
			$this->throwException("An error has occured while processing with speedex, please contact support.");
		}
		return $voucherId;
	}
	public function cancelShipment($sessionId, $voucherId)
	{
		/*$logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('cancelShipment');*/
        $this->writeLogs("cancelShipment");
		$xml_post_string  =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:spe="http://www.speedex.gr/">
			<soapenv:Header/>
			<soapenv:Body>
				<spe:CancelBOL>
					<spe:sessionID>'.$sessionId.'</spe:sessionID>
					<spe:voucherID>'.$voucherId.'</spe:voucherID>
				</spe:CancelBOL>
			</soapenv:Body>
		</soapenv:Envelope>';
		$response = $this->executeApi($xml_post_string)->CancelBOLResponse;
		$returnCode = (string)$response->returnCode;
		if($response->returnCode!="1"){
			// throw exception
			$this->throwException("An error has occured while processing with speedex, please contact support.");
		}
	}
	public function destroySession($sessionId)
	{
		/*$logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('destroySession');*/
        $this->writeLogs("destroySession");
		$xml_post_string  =
		'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:spe="http://www.speedex.gr/">
			<soapenv:Header/>
			<soapenv:Body>
				<spe:DestroySession>
					<!--Optional:-->
					<spe:sessionID>'.$sessionId.'</spe:sessionID>
				</spe:DestroySession>
			</soapenv:Body>
		</soapenv:Envelope>';
		$response = $this->executeApi($xml_post_string)->DestroySessionResponse;
		$returnCode = (string)$response->returnCode;
		if($response->returnCode!="1"){
			// throw exception
			$this->throwException("An error has occured while processing with speedex, please contact support.");
		}
	}
	private function throwException($message='')
	{
       	throw new \Magento\Framework\Exception\LocalizedException(__($message));
       	return;
	}
	private function getSpeedexConfigData ($type) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $data =  $_scopeConfig->getValue('speedex/general/'.$type, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        return $data;
	}
	private function executeApi ($xml_post_string) {
		$headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Content-length: ".strlen($xml_post_string),
        );
		$soapUrl = $this->getSpeedexConfigData("apiurl");
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $soapUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_USERPWD, $this->soapUser.":".$this->soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        $formatedResponse = $this->parseXML($response)->soap_Body;
        $logArray = array(
            "http_code" => $info["http_code"],
            "total_time" => $info["total_time"],
            "error" => $error,
            "info" => $info,
            "xml_post_string" => $xml_post_string,
            "response" => $formatedResponse
            );
		/*$logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug("executeApi: ". json_encode($logArray));*/
        $this->writeLogs("executeApi: ". json_encode($logArray));

        curl_close($ch);
        return $this->parseXML($response)->soap_Body;

	}
	public function writeLogs($data)
	 {
	 	$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/speedex.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info($data);
	 } 


	private function parseXML($xml) {
        $obj = SimpleXML_Load_String($xml);
        if ($obj === FALSE) return $xml;

        // GET NAMESPACES, IF ANY
        $nss = $obj->getNamespaces(TRUE);
        if (empty($nss)) return $xml;

        // CHANGE ns: INTO ns_
        $nsm = array_keys($nss);
        foreach ($nsm as $key)
        {
            // A REGULAR EXPRESSION TO MUNG THE XML
            $rgx
                = '#'               // REGEX DELIMITER
                . '('               // GROUP PATTERN 1
                . '\<'              // LOCATE A LEFT WICKET
                . '/?'              // MAYBE FOLLOWED BY A SLASH
                . preg_quote($key)  // THE NAMESPACE
                . ')'               // END GROUP PATTERN
                . '('               // GROUP PATTERN 2
                . ':{1}'            // A COLON (EXACTLY ONE)
                . ')'               // END GROUP PATTERN
                . '#'               // REGEX DELIMITER
            ;
            // INSERT THE UNDERSCORE INTO THE TAG NAME
            $rep
                = '$1'          // BACKREFERENCE TO GROUP 1
                . '_'           // LITERAL UNDERSCORE IN PLACE OF GROUP 2
            ;
            // PERFORM THE REPLACEMENT
            $xml =  preg_replace($rgx, $rep, $xml);
            $xml =  str_replace('soap-env', 'soap_env', $xml);
        }
        $obj = simplexml_load_string($xml);
        return $obj;
    }
}