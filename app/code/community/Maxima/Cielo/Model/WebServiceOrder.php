<?php
	
	class Maxima_Cielo_Model_WebServiceOrder
	{
		public $ccType;								// bandeira do cartao de credito
		public $paymentType;						// forma de pagameto (debito, credito - a vista ou parcelado)
		public $paymentParcels;						// numero de parcelas
		
		public $clientOrderNumber;					// clientOrderNumber
		public $clientOrderValue;					// clientOrderValue
		public $clientOrderCurrency = "986";		// numero de indice da moeda utilizada (R$)
		public $clientOrderDate;					// data da operacao
		public $clientOrderDescription;				// descricao
		public $clientOrderLocale = "PT";			// idioma
		
		public $cieloNumber;						// identificador da loja na cielo
		public $cieloKey;							// chave da loja a cielo
		
		public $capture;							// flag indicando quando pedido deve ser capturado
		public $autorize;							// flag indicando quando pedido deve ser autorizado
		public $postbackURL;						// url para qual o pagamento retornara o resultado da operacao
		public $tid;								// id da transacao
		public $status;								// status da transacao
		private $_xmlResponse;						// texto xml vindo da resposta da transacao
		private $_transactionError;					// erro ocorrido na transicao
		
		private $_webServiceURL;					// url do webservice da cielo
		private $_SSLCertificatePath;				// caminho no sistema de arquivos do certificado SSL
		private $_URLAuthTag = "url-autenticacao";	// tag que armazena a url de autenticacao da transacao
		
		const ENCODING = "ISO-8859-1";				// codificacao do xml
		const VERSION = "1.1.0";					// versao do webservice da cielo
		
		
		function __construct($params)
		{
			$baseURL 			= (isset($params['enderecoBase']))			? $params['enderecoBase'] 			: "https://qasecommerce.cielo.com.br";
			$certificatePath 	= (isset($params['caminhoCertificado']))	? $params['caminhoCertificado'] 	: Mage::getModuleDir('', 'Maxima_Cielo') . "/ssl/VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt";
			
			$this->_webServiceURL = $baseURL . "/servicos/ecommwsec.do";
			$this->_SSLCertificatePath = $certificatePath;
		}
		
		
		/**
		 *
		 * funcao utilizada para atribuir os valores base
		 * do pedido da cielo
		 * 
		 * @param string $index
		 * @param string $value
		 * 
		 * ou
		 * 
		 * @param array $index
		 */
		
		public function setData($index, $value = null)
		{
			if(is_array($index))
			{
				foreach($index as $i => $v)
				{
					$this->$i = $v;
				}
			}
			else
			{
				$this->$index = $value;
			}
		}
		
		
		
		/**
		 *
		 * funcao responsavel por montar o xml de requisicao e 
		 * realizar a criacao da transacao na cielo
		 * 
		 * @param boolean $ownerIncluded
		 * @return boolean
		 * 
		 */
		
		public function requestTransaction($ownerIncluded)
		{
			$msg  = $this->_getXMLHeader() . "\n";
			
			$msg .= '<requisicao-transacao id="' . md5(date("YmdHisu")) . '" versao="' . self::VERSION . '">' . "\n   ";
			$msg .= $this->_getXMLCieloData() . "\n   ";
			$msg .= $this->_getXMLOwnerData($ownerIncluded) . "\n   ";
			$msg .= $this->_getXMLOrderData() . "\n   ";
			$msg .= $this->_getXMLPaymentData() . "\n   ";
			$msg .= $this->_getXMLPostbackURL() . "\n   ";
			$msg .= $this->_getXMLAutorize() . "\n   ";
			$msg .= $this->_getXMLCapture() . "\n   ";
			$msg .= '</requisicao-transacao>';
			
			$maxAttempts = 3;
			
			while($maxAttempts > 0)
			{
				if($this->_sendRequest("mensagem=" . $msg, "Transacao"))
				{
					$xml = simplexml_load_string($this->_xmlResponse);
					
					// pega dados do xml
					$this->tid = (string) $xml->tid;
					$URLAuthTag = $this->_URLAuthTag;
					
					return ((string) $xml->$URLAuthTag);
				}
				
				$maxAttempts--;
			}
			
			return false;
		}
		
		
		/**
		 *
		 * funcao responsavel por montar o xml de requisicao e 
		 * realizar a consulta do status da transacao
		 * 
		 * @return boolean | string
		 * 
		 */
		 
		public function requestConsultation()
		{
			$msg  = $this->_getXMLHeader() . "\n";
			$msg .= '<requisicao-consulta id="' . md5(date("YmdHisu")) . '" versao="' . self::VERSION . '">' . "\n   ";
			$msg .= '<tid>' . $this->tid . '</tid>' . "\n   ";
			$msg .= $this->_getXMLCieloData() . "\n   ";
			$msg .= '</requisicao-consulta>';
			
			$maxAttempts = 3;
			
			while($maxAttempts > 0)
			{
				if($this->_sendRequest("mensagem=" . $msg, "Consulta"))
				{
					if($this->_hasConsultationError())
					{
						Mage::log($this->_transactionError);
						return false;
					}
					
					$xml = simplexml_load_string($this->_xmlResponse);
					$this->status = (string) $xml->status;
					
					return $this->status;
				}
				
				$maxAttempts--;
			}
			
			return false;
		}
		
		/**
		 *
		 * funcao responsavel por conferir se houve erro na requisicao
		 * 
		 * @return boolean
		 * 
		 */
		
		private function _hasConsultationError()
		{
			// certificao SSL invalido
			if(stripos($this->_xmlResponse, "SSL certificate problem") !== false)
			{
				$this->_transactionError = "Certificado SSL inválido.";
				return true;
			}
			
			$xml = simplexml_load_string($this->_xmlResponse);
			
			// tempo de requisicao expirou
			if($xml == null)
			{
				$this->_transactionError = "Tempo de espera na requisição expirou.";
				return true;
			}
			
			// retorno de erro da cielo
			if($xml->getName() == "erro")
			{
				$this->_transactionError = "[CIELO: " . $xml->codigo . "] " . $xml->mensagem;
				return true;
			}
			
			return false;
		}
		
		
		/**
		 *
		 * retorna a msg de erro da requisicao
		 * 
		 * @return string
		 * 
		 */
		
		public function getError()
		{
			return $this->_transactionError;
		}
		
		
		/**
		 *
		 * funcao que realiza a requisicao
		 * 
		 * @param string $postMsg
		 * @param string $transacao
		 * 
		 * @return string | boolean
		 * 
		 */
		
		public function _sendRequest($postMsg, $transacao)
		{
			$curl_session = curl_init();
			
			curl_setopt($curl_session, CURLOPT_URL, $this->_webServiceURL);
			curl_setopt($curl_session, CURLOPT_FAILONERROR, true);
			curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($curl_session, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($curl_session, CURLOPT_CAINFO, $this->_SSLCertificatePath);
			curl_setopt($curl_session, CURLOPT_SSLVERSION, 3);
			curl_setopt($curl_session, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($curl_session, CURLOPT_TIMEOUT, 40);
			curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl_session, CURLOPT_POST, true);
			curl_setopt($curl_session, CURLOPT_POSTFIELDS, $postMsg );
			
			$this->_xmlResponse = curl_exec($curl_session);
			
			if(!$this->_xmlResponse)
			{
				//Mage::log("curl: " . curl_error($curl_session));
				return false;
			}
			
			curl_close($curl_session);
			
			return true;
		}
		
		
		/**
		 *
		 * funcoes que montam o conteudo xml da requisicao
		 * 
		 * @return string
		 * 
		 */
		
		private function _getXMLHeader()
		{
			return '<?xml version="1.0" encoding="' . self::ENCODING . '" ?>'; 
		}
		
		private function _getXMLCieloData()
		{
			$msg = '<dados-ec>' . "\n      " .
						'<numero>'
							. $this->cieloNumber . 
						'</numero>' . "\n      " .
						'<chave>'
							. $this->cieloKey .
						'</chave>' . "\n   " .
					'</dados-ec>';
							
			return $msg;
		}
		
		private function _getXMLOwnerData($ownerIncluded)
		{
			return "";
			
			/*
			if(!$ownerIncluded)		return "";
			
			$msg = '<dados-portador>' . "\n      " . 
						'<numero>' 
							. $this->dadosPortadorNumero .
						'</numero>' . "\n      " .
						'<validade>'
							. $this->dadosPortadorVal .
						'</validade>' . "\n      " .
						'<indicador>'
							. $this->dadosPortadorInd .
						'</indicador>' . "\n      " .
						'<codigo-seguranca>'
							. $this->dadosPortadorCodSeg .
						'</codigo-seguranca>' . "\n   ";
			
			// Verifica se Nome do Portador foi informado
			if($this->dadosPortadorNome != null && $this->dadosPortadorNome != "")
			{
				$msg .= '   <nome-portador>'
							. $this->dadosPortadorNome .
						'</nome-portador>' . "\n   " ;
			}
			
			$msg .= '</dados-portador>';
			
			return $msg;
			*/
		}
		
		private function _getXMLOrderData()
		{
			$this->clientOrderDate = date("Y-m-d") . "T" . date("H:i:s");
			
			$msg = '<dados-pedido>' . "\n      " .
						'<numero>'
							. $this->clientOrderNumber . 
						'</numero>' . "\n      " .
						'<valor>'
							. $this->clientOrderValue.
						'</valor>' . "\n      " .
						'<moeda>'
							. $this->clientOrderCurrency .
						'</moeda>' . "\n      " .
						'<data-hora>'
							. $this->clientOrderDate .
						'</data-hora>' . "\n      ";
			
			if($this->clientOrderDescription != null && $this->clientOrderDescription != "")
			{
				$msg .= '<descricao>'
					. $this->clientOrderDescription .
					'</descricao>' . "\n      ";
			}
			
			$msg .= '<idioma>'
						. $this->clientOrderLocale .
					'</idioma>' . "\n   " .
					'</dados-pedido>';
							
			return $msg;
		}
		
		private function _getXMLPaymentData()
		{
			$msg = '<forma-pagamento>' . "\n      " .
						'<bandeira>' 
							. $this->ccType .
						'</bandeira>' . "\n      " .
						'<produto>'
							. $this->paymentType .
						'</produto>' . "\n      " .
						'<parcelas>'
							. $this->paymentParcels .
						'</parcelas>' . "\n   " .
					'</forma-pagamento>';
							
			return $msg;
		}
		
		private function _getXMLPostbackURL()
		{
			$msg = '<url-retorno>' . $this->postbackURL . '</url-retorno>';
			
			return $msg;
		}
		
		private function _getXMLAutorize()
		{
			$msg = '<autorizar>' . $this->autorize . '</autorizar>';
			
			return $msg;
		}
		
		private function _getXMLCapture()
		{
			$msg = '<capturar>' . $this->capture . '</capturar>';
			
			return $msg;
		}
	}
	
?>