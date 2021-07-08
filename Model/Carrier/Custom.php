<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\Alabamart\Model\Carrier;

use Custom\Alabamart\Helper\Data;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface as CarrierInterfaceAlias;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Directory\Model\RegionFactory;
class Custom extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{

    protected $_code = 'custom';

    protected $_isFixed = true;

    protected $_rateResultFactory;

    protected $_rateMethodFactory;

    protected $regionFactory;
	
    /**
	* @var Data
	*/
 
	private $_helper;
	/**
	 * @var array
	 */
	private $data;
	
	
	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
	 * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
	 * @param \Magento\Directory\Model\RegionFactory $regionFactory
	 * @param Data $helper
	 * @param array $data
	 */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        RegionFactory $regionFactory,
        Data $helper,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->regionFactory = $regionFactory;
	    $this->_helper = $helper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
    	
	    $this->_logger->debug('Click and Ship rate loaded');
	
	    if (!$this->getConfigFlag('active')) {
		    return false;
	    }
	
	
	    $this->_logger->debug('Click and Ship rate loaded');
	    $this->_logger->debug(__LINE__);
	
	    // exclude Virtual products price from Package value if pre-configured
	    if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
		    foreach ($request->getAllItems() as $item) {
			    if ($item->getParentItem()) {
				    continue;
			    }
			    if ($item->getHasChildren() && $item->isShipSeparately()) {
				    foreach ($item->getChildren() as $child) {
					    if ($child->getProduct()->isVirtual()) {
						    $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
					    }
				    }
			    } elseif ($item->getProduct()->isVirtual()) {
				    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
			    }
		    }
	    }
	    $this->_logger->debug(__LINE__);
	
	
	   	$getRegionCode =  $request->getDestRegionCode();
    	//$getRegionCode = $request->getDestCity();
	    $weight = $request->getPackageWeight();
	    
	    $this->_logger->debug($weight);
	    $this->_logger->debug(__LINE__);
	    $this->_logger->debug($getRegionCode);
	    
	    $response = $this->_helper->getClickNShipDeliveryFee($getRegionCode, $weight);
	    
	    $shippingPrice = $response;
	    $this->_logger->debug(__LINE__);
	    $this->_logger->debug($getRegionCode);
	    //$this->getConfigData('price');

        $result = $this->_rateResultFactory->create();

        if ($shippingPrice !== false) {
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);
        }

        return $result;
    }

    /**
     * getAllowedMethods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
    
}
