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
			if($i == 1)
				$label = "&#192; vista (" . Mage::helper('core')->currency(($total), true, false) . ")";
			else
				$label = $i . "x sem juros (" . Mage::helper('core')->currency(($total / $i), true, false) . " cada)";
			
			$parcels[] = array("num" => $i, "label" => $this->htmlEscape($label));
		}
		
		return $parcels;
	}
}
