<?php

/*
 * Maxima Cielo Module - payment method module for Magento, integrating
 * the billing forms with a Cielo's gateway Web Service.
 * Copyright (C) 2012  Fillipe Almeida Dutra
 * Belo Horizonte, Minas Gerais - Brazil
 * 
 * Contact: lawsann@gmail.com
 * Project link: http://code.google.com/p/magento-maxima-cielo/
 * Group discussion: http://groups.google.com/group/cielo-magento
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Maxima_Cielo_Block_Failure extends Mage_Checkout_Block_Onepage_Success
{
    private $_order;
    private $_payment;
    
    
    /**
     *
     * Devolve a instancia do objeto do pedido
     * para esta compra
     * 
     */
    public function getOrder()
    {
        if(!$this->_order)
        {
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
        }
        
        return $this->_order;
    }
    
    /**
     *
     * Devolve o numero do pedido
     * no WebService da Cielo
     * 
     */
    public function getCieloTid()
    {
        if(!$this->_payment)
        {
            $this->_payment = $this->getOrder()->getPayment();
        }
        
        return $this->_payment->getAdditionalInformation('Cielo_tid');
    }
    
    /**
     *
     * Devolve o status do pedido
     * no WebService da Cielo
     * 
     */
    public function getCieloStatus()
    {
        if(!$this->_payment)
        {
            $this->_payment = $this->getOrder()->getPayment();
        }
        
        return $this->_payment->getAdditionalInformation('Cielo_status');
    }
	
	/**
     *
     * Devolve a mensagem de erro
     * no WebService da Cielo
     * 
     */
    public function getErrorMessage()
    {
        if(!$this->_payment)
        {
            $this->_payment = $this->getOrder()->getPayment();
        }
		
		return $this->_payment->getAdditionalInformation('Cielo_error');
    }
}
 
