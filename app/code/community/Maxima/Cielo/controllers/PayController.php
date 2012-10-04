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
			$autoCapture = Mage::getStoreConfig('payment/Maxima_Cielo_Cc/auto_capture');
			
			$this->loadLayout();
			$block = $this->getLayout()->getBlock('Maxima_Cielo.success');
			
			// realiza consulta ao status do pagamento
			$status = $webServiceOrder->requestConsultation();
			$xml = $webServiceOrder->getXmlResponse();
			$eci = (isset($xml->autenticacao->eci)) ? ((string) $xml->autenticacao->eci) : "";
			
			$block->setCieloStatus($status);
			$block->setCieloTid($webServiceOrder->tid);
			$payment->setAdditionalInformation('Cielo_tid', $webServiceOrder->tid);
			$payment->setAdditionalInformation('Cielo_status', $status);
			$payment->setAdditionalInformation('Cielo_cardType', $webServiceOrder->ccType);
			$payment->setAdditionalInformation('Cielo_installments', $webServiceOrder->paymentParcels);
			$payment->setAdditionalInformation('Cielo_eci', $eci);
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
				// se jah foi capturado e nao era pra ter sido, tem algo de errado
				if(!$autoCapture && $payment->getMethodInstance()->getCode() == "Maxima_Cielo_Cc")
				{
					Mage::log("[Cielo] Pedido foi capturado, enquanto o flag indicava que nao deveria ter sido.");
				}
				else
				{
					if($order->canInvoice() && !$order->hasInvoices())
					{
						$invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), array());
						$invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
						
						// envia email de confirmacao de fatura
						$invoice->sendEmail(true);
						$invoice->setEmailSent(true);
						$invoice->save();
					}
				}
			}
			// ainda em processo de autenticacao, nao faz nada... aguardar
			else if($block->getCieloStatus() == 10)
			{
				
			}
			// por algum motivo deu errado, deve tentar denovo
			else
			{
				
			}
			
			// envia email de nova compra
			$order->sendNewOrderEmail();
			$order->setEmailSent(true);
			$order->save();
			
			// limpa juros, caso nao tenha sido zerado
			$quote = Mage::getSingleton('checkout/session')->getQuote();
			
			if($quote)
			{
				$quote->setInterest(0.0);
				$quote->setBaseInterest(0.0);
			
				$quote->setTotalsCollectedFlag(false)->collectTotals();
				$quote->save();
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