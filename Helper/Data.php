<?php
	
	
	namespace Custom\Alabamart\Helper;
	
	
	use Magento\Framework\App\Config\ScopeConfigInterface;
	use Magento\Framework\HTTP\AsyncClientInterface;
	use Magento\Framework\HTTP\ZendClient;
	use Magento\Framework\Serialize\SerializerInterface;
	use Magento\Store\Model\StoreManagerInterface;
	use Magento\Framework\HTTP\ZendClientFactory;
	use Magento\Directory\Model\RegionFactory;
	
	class Data extends \Magento\Framework\App\Helper\AbstractHelper
	{
		const XML_PATH_TOKEN_URL  = 'custom_alabamart/general/token_url';
		const XML_PATH_TOKEN_PASSWORD = 'custom_alabamart/general/password';
		const XML_PATH_TOKEN_USERNAME = 'custom_alabamart/general/username';
		const XML_PATH_TOKEN_GRANT_TYPE = 'custom_alabamart/general/grant_type';
		const XML_PATH_DELIVERY_URL  = 'custom_alabamart/general/delivery_fee_url';
		const XML_PATH_DELIVERY_ORIGIN  = 'custom_alabamart/general/origin';
		
		
		const RESPONSE_DELIM_CHAR = '(~)';
		/**
		 * @var ZendClientFactory
		 */
		protected $httpClientFactory;
		/**
		 * @var \Psr\Log\LoggerInterface
		 */
		protected $_logger;
		
		/**
		 * @var \Magento\Framework\Module\ModuleListInterface
		 */
		protected $_moduleList;
		
		
		/**
		 * @var AsyncClientInterface
		 */
		protected $httpClient;
		
		/**
		 * @var SerializerInterface
		 */
		protected $serializer;
		/**
		 * @var \Magento\Framework\App\Helper\Context
		 */
		private $context;

		
		protected $regionFactory;
		
		/**
		 * @param \Magento\Framework\App\Helper\Context $context
		 * @param \Magento\Framework\Module\ModuleListInterface $moduleList
		 * @param ZendClient $httpClient
		 * @param SerializerInterface $serializer
		 * @param ScopeConfigInterface $scopeConfig
		 * @param ZendClientFactory $httpClientFactory
		 * @param RegionFactory $regionFactory
		 */
		public function __construct(
			\Magento\Framework\App\Helper\Context $context,
			\Magento\Framework\Module\ModuleListInterface $moduleList,
			ZendClient $httpClient,
			SerializerInterface $serializer,
			ScopeConfigInterface $scopeConfig,
			ZendClientFactory $httpClientFactory,
			RegionFactory $regionFactory
		) {
			$this->_logger      = $context->getLogger();
			$this->_moduleList  = $moduleList;
			$this->context = $context;
			$this->httpClient = $httpClient;
			$this->serializer = $serializer;
			$this->scopeConfig = $scopeConfig;
			$this->httpClientFactory = $httpClientFactory;
			$this->regionFactory = $regionFactory;
		}
		
		/**
		 * @return mixed
		 */
		public function getClickNShipTokenUrl()
		{
			return $this->scopeConfig->getValue(
				self::XML_PATH_TOKEN_URL,
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
				
			);
		}		/**
		 * @return mixed
		 */
		public function getClickNShipDeliveryFeeUrl()
		{
			return $this->scopeConfig->getValue(
				self::XML_PATH_DELIVERY_URL,
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
				
			);
		}
		
		
		public function getClickNShipTokenPassword()
		{
			return $this->scopeConfig->getValue(
				self::XML_PATH_TOKEN_PASSWORD,
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		}
		
		
		public function getClickNShipTokenUsername()
		{
			return $this->scopeConfig->getValue(
				self::XML_PATH_TOKEN_USERNAME,
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		}
		
		public function getClickNShipTokeGrantType()
		{
			return $this->scopeConfig->getValue(
				self::XML_PATH_TOKEN_GRANT_TYPE,
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		}
		
		public function getClickNShipDeliveryFeeOrigin()
		{
			return $this->scopeConfig->getValue(
				self::XML_PATH_DELIVERY_ORIGIN,
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		}
		/**
		 * @return |null
		 * @throws \Zend_Http_Client_Exception
		 */
		public function generateTokenClickNShip()
		{
			$responseData = null;

			$username = trim($this->getClickNShipTokenUsername());
			$password = trim($this->getClickNShipTokenPassword());
			$grantType = trim($this->getClickNShipTokeGrantType());
			
			$uri = trim($this->getClickNShipTokenUrl());
			
			$usernameAndPassword = base64_encode($username.':'.$password);
			$this->httpClient->setUri($uri);
			$this->httpClient->setHeaders('Authorization', 'Basic '.$usernameAndPassword);
			$this->httpClient->setHeaders('Content-Type', 'application/x-www-form-urlencoded');
			$this->httpClient->setParameterPost(['username'=> $username, 'password'=> $password, 'grant_type'=> $grantType]);
			$httpResult = $this->httpClient->request(\Zend_Http_Client::POST);
			try {
				
				$responseData = $httpResult->getRawBody();

			} catch (\Zend_Http_Client_Exception $e) {
				$this->_logger->critical($e->getMessage());
			}
			//echo $responseData;
			return $responseData;
		}
		
		
		/**
		 * @return string|null
		 * @throws \Zend_Http_Client_Exception
		 * @param $region
		 * @param $weight
		 */
		public function getClickNShipDeliveryFee($region = 'Abuja', $weight = '1.5')
		{
			
			$responseData = null;

			$uri = trim($this->getClickNShipDeliveryFeeUrl());

			$token = \json_decode($this->generateTokenClickNShip(), true);
			$accessToken = null;
			
			if (isset($token['access_token'])) {
				$accessToken = $token['access_token'];
			}
			
			
			if(isset($token['access_token'])){
				$accessToken = $token['access_token'];
			}

			$postData = [
				'Origin'=> trim($this->getClickNShipDeliveryFeeOrigin()),
				'Destination' => $region,
				'Weight' => $weight
			];
			
			$postData = json_encode($postData);
			
			if(isset($accessToken)) {
				$accessTokenData = $accessToken;
				$this->httpClient->setHeaders('Authorization', 'Bearer '.$accessTokenData);
			}
			$this->httpClient->setHeaders(  'Content-Type', 'text/plain');
			$this->httpClient->setHeaders('Content-Type', 'application/json');
			$this->httpClient->setRawData($postData);
			$this->httpClient->setUri($uri);
			$httpResult = $this->httpClient->request(\Zend_Http_Client::POST);

			try {
			
				$responseData = $httpResult->getRawBody();
				
			} catch (\Zend_Http_Client_Exception $e) {
				$this->_logger->critical($e->getMessage());
			}
			$response = json_decode($responseData);
			$shippingPrice = null;
			
			$responseDataInArray = (array) $response;
			
//			if (isset($responseDataInArray)) {
//				$shippingPrice = $responseDataInArray[0]->TotalAmount;
//			}
			
			$convertData = [];
			
			if (isset($responseDataInArray) && is_array($responseDataInArray)){
				foreach ($responseDataInArray as $data) {
					$convera = (array) $data;
					$convertData[] = $convera;
				}
			}
			
						
			if (isset($convertData['0'])){
				if (isset($convertData['0']['TotalAmount']))$shippingPrice = $convertData['0']['TotalAmount'];
				
			}
			
			return $shippingPrice;
		
		
		}
		
		
		
		
	
	}