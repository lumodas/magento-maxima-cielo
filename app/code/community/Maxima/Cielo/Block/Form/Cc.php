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
    	// pega dados de parcelamento
    	$maxInstallments = intval(Mage::getStoreConfig('payment/Maxima_Cielo_Cc/max_parcels_number'));
    	$minInstallmentValue = floatval(Mage::getStoreConfig('payment/Maxima_Cielo_Cc/min_parcels_value'));
		
		// pega dados de juros
		$withoutInterest = intval(Mage::getStoreConfig('payment/Maxima_Cielo_Cc/installment_without_interest'));
		$interestValue = floatval(Mage::getStoreConfig('payment/Maxima_Cielo_Cc/installment_interest_value'));
		
		// pega valores do pedido
		$total = Mage::getSingleton('checkout/cart')->getQuote()->getGrandTotal();
		
		$installments = array();
		
		for($i = 1; $i <= $maxInstallments; $i++)
		{
			// caso nao haja juros na parcela
			if($i <= $withoutInterest)
			{
				$orderTotal = $total;
				$installmentValue = $orderTotal / $i;
			}
			// caso haja juros
			else
			{
				$installmentValue = Mage::helper('Maxima_Cielo')->calcInstallmentValue($total, $interestValue / 100, $i);
				$orderTotal = $i * $installmentValue;
			}
			
			
			
			// confere se a parcela nao estah abaixo do minimo
			if($minInstallmentValue >= 0 && $installmentValue < $minInstallmentValue)
			{
				break;
			}
			
			// monta o texto da parcela
			if($i == 1)
			{
				$label = "&#192; vista (" . Mage::helper('core')->currency(($total), true, false) . ")";
			}
			else
			{
				if($i <= $withoutInterest)
				{
					$label = $i . "x sem juros (" . Mage::helper('core')->currency(($installmentValue), true, false) . " cada)";
				}
				else
				{
					$label = $i . "x (" . Mage::helper('core')->currency(($installmentValue), true, false) . " cada)";
				}
			}
			
			// adiciona no vetor de parcelas
			$installments[] = array("num" => $i, "label" => $this->htmlEscape($label));
		}
		
		return $installments;
	}
	
	/**
     * 
     * Retorna vetor com os codigos dos cartoes habilitados
     * 
     */
    
    public function getAllowedCards()
	{
    	$allowedCards = explode(",", Mage::getStoreConfig('payment/Maxima_Cielo_Cc/card_types'));
    	$allCards = Mage::getModel('Maxima_Cielo/cc_types')->toOptionArray();
    	
    	$validCards = array();
    	
    	foreach($allCards as $card)
    	{
			if(in_array($card['value'], $allowedCards))
			{
				$validCards[] = $card;
			}
    	}
    	
    	return $validCards;
	}
}
