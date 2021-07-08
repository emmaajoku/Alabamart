<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\Alabamart\Controller\Index;

use Custom\Alabamart\Helper\Data;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
	/**
	 * @var Data
	 */
	private $_helper;
	
	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 * @param Data $helper
	 */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
	    Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
	    $this->_helper = $helper;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
	    $response = $this->_helper->getClickNShipDeliveryFee();
		
	    echo $response;
    }
}

