<?php


class Maxima_Cielo_Block_Adminhtml_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{
    public function  __construct()
    {
		parent::__construct();
		
		$payment = $this->getOrder()->getPayment();
		$tid = $payment->getAdditionalInformation('Cielo_tid');
		
		
		if($tid)
		{
			$this->_addButton('maxima_cielo_consult', array
			(
				'label'     => Mage::helper('Maxima_Cielo')->__('Consult WebService'),
				'onclick'   => "loadCieloWebServiceData('" . $tid . "');",
				'class'     => 'go'
			));
			
			$this->_addButton('maxima_cielo_capture', array
			(
				'label'     => Mage::helper('Maxima_Cielo')->__('Capture'),
				'onclick'   => "captureCieloOrder('" . $tid . "', " . $this->getOrder()->getId() . ");",
				'class'     => 'go'
			));
		}
	}
}
 
