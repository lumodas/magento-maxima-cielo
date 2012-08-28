<?php

class Maxima_Cielo_Block_Verify extends Mage_Checkout_Block_Onepage_Success
{
    private $_cieloStatus = -1;
    private $_cieloTid = -1;
    
    
    /**
	 * 
	 * Define mensagem mostrada ao fim da compra
	 * 
	 * @return string
	 * 
	 */
    
    public function getCieloDataHtml()
    {
		$html = "";
		
		if($this->_cieloStatus == 6)
		{
			$html .= "Sua compra foi faturada com êxito.<br />O ID da sua transação na Cielo é <b>" . $this->_cieloTid . "</b>.";
		}
		else
		{
			$html .= "Seu pagamento não foi realizado com sucesso. Para maiores informações, por favor acesse o link do pedido 
					  acima ou entre em contato conosco.<br />O ID da sua transação na Cielo é <b>" . $this->_cieloTid . "</b>.";
		}
		
		return $html;
    }
    
    
    
    /**
     * 
     * Getters and Setters
     * 
     */
    
    public function setCieloStatus($st)
    {
		$this->_cieloStatus = $st;
    }
    
    public function getCieloStatus()
    {
		return $this->_cieloStatus;
    }
    
    public function setCieloTid($tid)
    {
		$this->_cieloTid = $tid;
    }
    
    public function getCieloTid()
    {
		return $this->_cieloTid;
    }
    
}
 
