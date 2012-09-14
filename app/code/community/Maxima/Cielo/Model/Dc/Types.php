<?php

class Maxima_Cielo_Model_Dc_Types
{

    /**
     * Formato vetor de vetores
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array
		(
			array
			(
				'value' 	=> 'visa',
				'label' 	=> Mage::helper('adminhtml')->__('Visa Electron (somente Bradesco)'),
				'image' 	=> 'Visa-Electron.png'
			)
        );
    }

    /**
     * Formato chave-valor
     *
     * @return array
     */
    public function toArray()
    {
        return array
		(
            'visa' 	=> Mage::helper('adminhtml')->__('Visa Electron')
        );
    }
	
	/**
     * Formato chave
     *
     * @return array
     */
    public function getCodes()
    {
        return array
		(
            'visa'
        );
    }
}