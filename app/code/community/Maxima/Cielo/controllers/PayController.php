<?php
	class Maxima_Cielo_PayController extends Mage_Core_Controller_Front_Action
	{        
		/**
		 * 
		 * Funcao responsavel por tratar o retorno da pagina de pagamento da Cielo.
		 * Confere a informacao retornada, limpa o objeto de requisicao da sessao e 
		 * exibe mensagem com o resultado da acao.
		 * 
		 */
		
		public function verifyAction()
		{
			if(!Mage::getSingleton('core/session')->getData('cielo-transaction'))
			{
				$url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
				Mage::app()->getFrontController()->getResponse()->setRedirect($url);
				return;
			}
			
			$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
			$order = Mage::getModel('sales/order')->load($orderId);
			$payment = $order->getPayment();
			
			// pega o pedido armazenado
			$webServiceOrder = Mage::getSingleton('core/session')->getData('cielo-transaction');
			Mage::getSingleton('core/session')->unsetData('cielo-transaction');
			
			$this->loadLayout();
			$block = $this->getLayout()->getBlock('Maxima_Cielo.success');
			
			// realiza consulta ao status do pagamento
			$block->setCieloStatus($webServiceOrder->requestConsultation());
			$block->setCieloTid($webServiceOrder->tid);
			$payment->setAdditionalInformation('Cielo_tid', $block->getCieloTid());
			$payment->setAdditionalInformation('Cielo_status', $block->getCieloStatus());
			$payment->save();
			
			// possiveis status 
			// -1 nao foi possivel consultar
			// 0 criada
			// 1 em andamento
			// 2 autenticada
			// 3 nao autenticada
			// 4 autorizada ou pendente de captura
			// 5 nao autorizada
			// 6 capturada
			// 8 nao capturada
			// 9 cancelada
			// 10 em autenticacao
			
			// tudo ok, transacao aprovada, salva no banco
			if($block->getCieloStatus() == 6)
			{
				if($order->canInvoice() && !$order->hasInvoices())
				{
					$invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), array());
					$invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
				}
			}
			// ainda em processo de autenticacao, nao faz nada... aguardar
			else if($block->getCieloStatus() == 10) { }
			// por algum motivo foi cancelada, deve tentar denovo
			else if($block->getCieloStatus() == 0)
			{
				
			}
			// por algum motivo deu errado, deve tentar denovo
			else
			{
				
			}
			
			$this->renderLayout();
		}
		
		/**
		 * 
		 * Funcao responsavel por tratar o caso de erro na comunicacao com o servidor da 
		 * Cielo. Limpa objeto da sessao e mostra mensagem de erro.
		 * 
		 */
		
		public function failureAction()
		{
			if(!Mage::getSingleton('core/session')->getData('cielo-transaction'))
			{
				$url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
				Mage::app()->getFrontController()->getResponse()->setRedirect($url);
				return;
			}
			
			$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
			$order = Mage::getModel('sales/order')->load($orderId);
			$payment = $order->getPayment();
			
			// pega o pedido armazenado
			$webServiceOrder = Mage::getSingleton('core/session')->getData('cielo-transaction');
			Mage::getSingleton('core/session')->unsetData('cielo-transaction');
			
			$this->loadLayout();
			$block = $this->getLayout()->getBlock('Maxima_Cielo.failure');
			
			// preenche erro
			$payment->setAdditionalInformation('Cielo_error', true);
			$payment->setAdditionalInformation('Cielo_error_msg', $webServiceOrder->getError());
			$payment->save();
			
			$this->renderLayout();
		}
	}
?>
