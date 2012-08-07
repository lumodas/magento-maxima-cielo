<?php


class Maxima_Cielo_Model_Dc extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'Maxima_Cielo_Dc';
    protected $_formBlockType = 'Maxima_Cielo/form_dc';
    protected $_infoBlockType = 'Maxima_Cielo/info_dc';
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
        
        // salva a bandeira
		$info = $this->getInfoInstance();
        $info->setCcType($data->getCcType());
		
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
     *  Setter da instancia do pedido
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
     * Formata o valor da compra de acordo com a definicao da Cielo
     *
     * @param   string $originalValue
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
			'paymentType'		=> 'A',
			'paymentParcels'	=> 1,
		);
		
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
