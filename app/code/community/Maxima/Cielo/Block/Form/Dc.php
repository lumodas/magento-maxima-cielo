<?php

class Maxima_Cielo_Block_Form_Dc extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cielo/form/dc.phtml');
    }
}
