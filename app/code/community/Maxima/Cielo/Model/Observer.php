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

class Maxima_Cielo_Model_Observer
{
	public function adminOrderTransaction($observer)
	{
		$event = $observer->getEvent();
		$quote = $event->getQuote();
		$paymentMethod = $quote->getPayment()->getMethodInstance();
		
		// caso seja buy page loja, jah realiza a abertura da transacao na cielo
		if($paymentMethod->getConfigData('buypage', $paymentMethod->getStoreId()) == "loja")
		{
			$paymentMethod->getOrderPlaceRedirectUrl();
		}
	}
	
}
