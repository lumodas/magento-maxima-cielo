<?php

class Maxima_Cielo_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    /**
     * Formata o valor da compra de acordo com a definicao da Cielo
     *
     * @param   string $originalValue
     * @return  string
     */
    public function formatValueForCielo($originalValue)
    {
		if(strpos($originalValue, ".") == false)
		{
			$value = $originalValue . "00";
		}
		else
		{
			list($integers, $decimals) = explode(".", $originalValue);
			
			if(strlen($decimals) > 2)
			{
				$decimals = substr($decimals, 0, 2);
			}
			
			while(strlen($decimals) < 2)
			{
				$decimals .= "0";
			}
			
			$value = $integers . $decimals;
		}
		
		return $value;
    }
    
    /**
     * Retorna mensagem adequada ao codigo de retorno da cielo
     *
     * @param   string $originalValue
     * @return  string
     */
    public function getStatusMessage($statusCode)
    {
		switch($statusCode)
		{
			case 1:
				return "Em andamento";
			case 2:
				return "Autenticada";
			case 3:
				return "Não autenticada";
			case 4:
				return "Pendente";
			case 5:
				return "Não autorizada";
			case 6:
				return "Concluída";
			case 8:
				return "Não capturada";
			case 9:
				return "Cancelada";
			case 10:
				return "Em autenticação";
			default:
				return "Erro (" . $statusCode . ")";
		}
    }
    
}
