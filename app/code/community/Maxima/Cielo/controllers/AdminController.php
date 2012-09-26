<?php
	class Maxima_Cielo_AdminController extends Mage_Adminhtml_Controller_Action
	{
		/**
		 * 
		 * Funcao responsavel por consultar o status de uma transacao no WebService da 
		 * Cielo
		 * 
		 */
	
		public function consultAction()
		{
			// verifica se o usuario estah logado na administracao do magento
			Mage::getSingleton('core/session', array('name' => 'adminhtml'));
			$session = Mage::getSingleton('admin/session');
			
			if (!$session->isLoggedIn())
			{
				return;
			}
			
			// pega pedido correspondente
			$orderId = $this->getRequest()->getParam('order');
			$order = Mage::getModel('sales/order')->load($orderId);
			
			// pega os dados para requisicao e realiza a consulta
			$methodCode = $order->getPayment()->getMethodInstance()->getCode();
			$cieloNumber 		= Mage::getStoreConfig('payment/' . $methodCode . '/cielo_number');
			$cieloKey 			= Mage::getStoreConfig('payment/' . $methodCode . '/cielo_key');
			
			$model = Mage::getModel('Maxima_Cielo/webServiceOrder');
			
			$model->tid = $this->getRequest()->getParam('tid');
			$model->cieloNumber = $cieloNumber;
			$model->cieloKey = $cieloKey;
			
			$model->requestConsultation();
			$xml = $model->getXmlResponse();
			
			$this->getResponse()->setBody(Mage::helper('Maxima_Cielo')->xmlToHtml($xml));
		}
		
		
		/**
		 * 
		 * Funcao responsavel por enviar o pedido de captura para o WebService da Cielo
		 * 
		 */
	
		public function captureAction()
		{
			// verifica se o usuario estah logado na administracao do magento
			Mage::getSingleton('core/session', array('name' => 'adminhtml'));
			$session = Mage::getSingleton('admin/session');
			
			if (!$session->isLoggedIn())
			{
				return;
			}
			
			// pega pedido correspondente
			$orderId = $this->getRequest()->getParam('order');
			$order = Mage::getModel('sales/order')->load($orderId);
			$value = Mage::helper('Maxima_Cielo')->formatValueForCielo($order->getGrandTotal());
			
			// pega os dados para requisicao e realiza a consulta
			$cieloNumber 		= Mage::getStoreConfig('payment/Maxima_Cielo_Cc/cielo_number');
			$cieloKey 			= Mage::getStoreConfig('payment/Maxima_Cielo_Cc/cielo_key');
			
			$model = Mage::getModel('Maxima_Cielo/webServiceOrder');
			
			$model->tid = $this->getRequest()->getParam('tid');
			$model->cieloNumber = $cieloNumber;
			$model->cieloKey = $cieloKey;
			
			// requisita captura
			$model->requestCapture($value);
			$xml = $model->getXmlResponse();
			$status = (string) $xml->status;
			
			// tudo ok, transacao aprovada, cria fatura
			if($status == 6)
			{
				$html = "<b>Pedido capturado com sucesso!</b> &nbsp; &nbsp; 
						<button type=\"button\" title=\"Atualizar Informações\" onclick=\"document.location.reload(true)\">
							<span>Recarregar Página</span>
						</button><br /><br />";
				
				// atualiza os dados da compra
				$payment = $order->getPayment();
				$payment->setAdditionalInformation('Cielo_status', $status);
				$payment->save();
				
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
			else
			{
				$html = "";
			}
			
			$this->getResponse()->setBody($html . Mage::helper('Maxima_Cielo')->xmlToHtml($xml));
		}
		
		
		/**
		 * 
		 * Funcao responsavel por enviar o pedido de cancelamento para o WebService da Cielo
		 * 
		 */
	
		public function cancelAction()
		{
			// verifica se o usuario estah logado na administracao do magento
			Mage::getSingleton('core/session', array('name' => 'adminhtml'));
			$session = Mage::getSingleton('admin/session');
			
			if (!$session->isLoggedIn())
			{
				return;
			}
			
			
			// pega os dados para requisicao e realiza a consulta
			$cieloNumber 		= Mage::getStoreConfig('payment/Maxima_Cielo_Cc/cielo_number');
			$cieloKey 			= Mage::getStoreConfig('payment/Maxima_Cielo_Cc/cielo_key');
			
			$model = Mage::getModel('Maxima_Cielo/webServiceOrder');
			
			$model->tid = $this->getRequest()->getParam('tid');
			$model->cieloNumber = $cieloNumber;
			$model->cieloKey = $cieloKey;
			
			// requisita cancelamento
			$model->requestCancellation();
			$xml = $model->getXmlResponse();
			
			$this->getResponse()->setBody(Mage::helper('Maxima_Cielo')->xmlToHtml($xml));
		}
	} 
