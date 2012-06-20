<?php

class Maxima_Cielo_Block_Form_Cc extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cielo/form/cc.phtml');
    }
    
    
    /**
     * 
     * Lista opcoes de parcelamento
     * 
     */
    
    public function getInstallments()
	{
    	$max_parcels = Mage::getStoreConfig('payment/Maxima_Cielo_Cc/max_parcels_number');
		
		$total = Mage::getSingleton('checkout/cart')->getQuote()->getGrandTotal();
		$totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
		
		$parcels = array();
		
		for($i = 1; $i <= $max_parcels; $i++)
		{
			$parcels[] = array("num" => $i, "label" => $i . "x");
		}
		
		return $parcels;
	}
    
}
