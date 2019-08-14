<?php
class MondialRelay_Pointsrelais_Model_Carrier_Pointsrelais extends Mage_Shipping_Model_Carrier_Abstract
{
	protected $_code = 'pointsrelais';

	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
        if (!$this->getConfigFlag('active')) {
            return false;
        }
		
		// Permet de désactiver le module pour le multi-shipping

		if (Mage::getSingleton('checkout/session')->getQuote()->getIsMultiShipping()) 
		{
            return false;
		}

        if (!$request->getConditionName()) {
            $request->setConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
        }

        $result = Mage::getModel('shipping/rate_result');
        
        $rates = $this->getRate($request);
        
        foreach($rates as $rate)
        {
            if (!empty($rate) && $rate['price'] >= 0) 
            {

/*---------------------------------------- Liste des points relais -----------------------------------------*/

				// On met en place les paramètres de la requète
				$params = array(
							   'Enseigne'     => $this->getConfigData('enseigne'),
							   'Pays'         => $request->_data['dest_country_id'],
							   'CP'           => $request->_data['dest_postcode'],
				);
				
				//On crée le code de sécurité
				$code = implode("",$params);
				$code .= $this->getConfigData('cle');
				
				//On le rajoute aux paramètres
				$params["Security"] = strtoupper(md5($code));
				
				// On se connecte
				$client = new SoapClient("http://www.mondialrelay.fr/WebService/Web_Services.asmx?WSDL");
				
				// Et on effectue la requète
				$points_relais = $client->WSI2_RecherchePointRelais($params)->WSI2_RecherchePointRelaisResult;
				
				
				// On créé une méthode de livraison par point relais
				foreach( $points_relais as $point_relais ) {
					if ( is_object($point_relais) ) {
						
						$method = Mage::getModel('shipping/rate_result_method');

						$method->setCarrier('pointsrelais');
						$method->setCarrierTitle($this->getConfigData('title'));
						
						$methodTitle = $point_relais->LgAdr1 . ' - ' . $point_relais->Ville  . ' <a href="#" onclick="PointsRelais.showInfo(\'' . $point_relais->Num . '\'); return false;">Détails</a> - <span style="display:none;" id="pays">' . $request->_data['dest_country_id'] . '</span>';
						$method->setMethod($point_relais->Num);
						$method->setMethodTitle($methodTitle);
		
						$price = $rate['price'];
					  
						$method->setPrice($this->getFinalPriceWithHandlingFee($price));
						$method->setCost($rate['cost']);
		
						$result->append($method);
					}
				}
            }            
        }

        return $result;
	}
	
	public function getRate(Mage_Shipping_Model_Rate_Request $request)
	{
		return Mage::getResourceModel('pointsrelais/carrier_pointsrelais')->getRate($request);
	}	

	public function getCode($type, $code='')
    {
        $codes = array(

            'condition_name'=>array(
                'package_weight' => Mage::helper('shipping')->__('Weight vs. Destination'),
                'package_value'  => Mage::helper('shipping')->__('Price vs. Destination'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items vs. Destination'),
            ),

            'condition_name_short'=>array(
                'package_weight' => Mage::helper('shipping')->__('Poids'),
                'package_value'  => Mage::helper('shipping')->__('Valeur du panier'),
                'package_qty'    => Mage::helper('shipping')->__('Nombre d\'articles'),
            ),

        );

        if (!isset($codes[$type])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Tablerate Rate code type: %s', $type));
        }

        if (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Tablerate Rate code for type %s: %s', $type, $code));
        }

        return $codes[$type][$code];
    }

    public function getAllowedMethods()
    {
        return array('pointsrelais'=>$this->getConfigData('name'));
    }

	public function isTrackingAvailable()
	{
		return true;
	}
	
	public function getTrackingInfo($tracking_number)
	{
		$tracking_result = $this->getTracking($tracking_number);

		if ($tracking_result instanceof Mage_Shipping_Model_Tracking_Result)
		{
			if ($trackings = $tracking_result->getAllTrackings())
			{
				return $trackings[0];
			}
		}
		elseif (is_string($tracking_result) && !empty($tracking_result))
		{
			return $tracking_result;
		}
		
		return false;
	}
	
	protected function getTracking($tracking_number)
	{
		$key = '<' . $this->getConfigData('marque') .'>' . $tracking_number . '<' . $this->getConfigData('cle') . '>';
		$key = md5($key);
		
		$tracking_url = 'http://www.mondialrelay.fr/lg_fr/espaces/url/popup_exp_details.aspx?cmrq=' . strtoupper($this->getConfigData('marque')) .'&nexp=' . strtoupper($tracking_number) . '&crc=' . strtoupper($key) ;

		$tracking_result = Mage::getModel('shipping/tracking_result');

		$tracking_status = Mage::getModel('shipping/tracking_result_status');
		$tracking_status->setCarrier($this->_code)
						->setCarrierTitle($this->getConfigData('title'))
						->setTracking($tracking_number)
						->setUrl($tracking_url);
		$tracking_result->append($tracking_status);

		return $tracking_result;
	}

}