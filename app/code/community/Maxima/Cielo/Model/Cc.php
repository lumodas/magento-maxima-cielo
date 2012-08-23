<?php


class Maxima_Cielo_Model_Cc extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'Maxima_Cielo_Cc';
    protected $_formBlockType = 'Maxima_Cielo/form_cc';
    protected $_infoBlockType = 'Maxima_Cielo/info_cc';
    protected $_canUseInternal = true;
    protected $_canUseForMultishipping = false;
    
    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object))
		{
            $data = new Varien_Object($data);
        }
        
        // salva a bandeira e o numero de parcelas
		$info = $this->getInfoInstance();
        $additionaldata = array('parcels_number' => $data->getParcelsNumber());
        $info->setCcType($data->getCcType())
            ->setAdditionalData(serialize($additionaldata));
		
        return $this;
    }
	
    
    /**
     *  Getter da instancia do pedido
     *
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {}
		
        return $this->_order;
    }

    /**
     *  Setter do pedido
     *
     *  @param Mage_Sales_Model_Order $order
     */
    public function setOrder($order)
    {
        if ($order instanceof Mage_Sales_Model_Order)
		{
            $this->_order = $order;
        }
		elseif (is_numeric($order))
		{
            $this->_order = Mage::getModel('sales/order')->load($order);
        }
		else
		{
            $this->_order = null;
        }
        return $this;
    }
    
    
    
	
	
	/**
     * Abre transacao com a Cielo para uma compra e redirectiona para a 
     * pagina de pagamento na Cielo. Em caso de erro, redireciona para pagina
     * de erro.
     *
     * @return  string
     */
	public function getOrderPlaceRedirectUrl()
	{
		$info = $this->getInfoInstance();
		$order = $info->getQuote();
		$storeId = $this->getStoreId();
		$payment = $order->getPayment();
		$additionaldata = unserialize($payment->getData('additional_data'));

		// coleta os dados necessarios
		$value 				= Mage::helper('Maxima_Cielo')->formatValueForCielo($order->getGrandTotal());
		$paymentType 		= $additionaldata["parcels_number"];
		$ccType 			= $payment->getCcType();
		$paymentParcels 	= $this->getConfigData('installments_type', $storeId);
		$cieloNumber 		= $this->getConfigData('cielo_number', $storeId);
		$cieloKey 			= $this->getConfigData('cielo_key', $storeId);
		$environment 		= $this->getConfigData('environment', $storeId);
		$sslFile	 		= $this->getConfigData('ssl_file', $storeId);
		
		// cria instancia do pedido
		$webServiceOrder = Mage::getModel('Maxima_Cielo/webServiceOrder', array('enderecoBase' => $environment, 'caminhoCertificado' => $sslFile));
		
		// preenche dados coletados
		$webServiceOrderData = array
		(
			'ccType'			=> $ccType,
			'cieloNumber'		=> $cieloNumber,
			'cieloKey'			=> $cieloKey,
			'capture'			=> 'true',
			'autorize'			=> '1',
			'clientOrderNumber'	=> $payment->getId(),
			'clientOrderValue'	=> $value,
			'postbackURL'		=> Mage::getUrl('cielo/pay/verify'),
		);
		
		// conforme mostrado no manual versao 2.0, pagina 11,
		// caso o cartao seja Dinners, Discover, Elo ou Amex
		// o valor do flag autorizar deve ser 3
		if($ccType == "diners" || 
		   $ccType == "discover" || 
		   $ccType == "elo" || 
		   $ccType == "amex")
		{
			$webServiceOrderData['autorize'] = '3';
		}
		
		if($paymentType == "1")
		{
			$webServiceOrderData['paymentType'] = $paymentType;
			$webServiceOrderData['paymentParcels'] = 1;
		}
		else
		{
			$webServiceOrderData['paymentType'] = $paymentParcels;
			$webServiceOrderData['paymentParcels'] = $paymentType;
		}
		
		$webServiceOrder->setData($webServiceOrderData);
		
		
		$redirectUrl = $webServiceOrder->requestTransaction(false);
		Mage::getSingleton('core/session')->setData('cielo-transaction', $webServiceOrder);
		
		if($redirectUrl == false)
		{
			return Mage::getUrl('cielo/pay/failure');
		}
		else
		{
			return $redirectUrl;
		}
    }
}
