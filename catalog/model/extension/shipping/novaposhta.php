<?php

class ModelExtensionShippingNovaPoshta extends Model
{
    public function getQuote($address)
    {
        $this->loadSettings();
        $this->load->language('extension/shipping/novaposhta');
        $this->document->addScript('catalog/view/javascript/novaposhta/model.js');
        $this->document->addScript('catalog/view/javascript/select2/dist/js/select2.full.min.js');
        $this->document->addStyle('catalog/view/javascript/select2/dist/css/select2.min.css', 'stylesheet', '');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_novaposhta_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if (!$this->config->get('shipping_novaposhta_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $error = '';
        $api_key = $this->config->get('shipping_novaposhta_api');
        $quote_data = array();

        $allServicesTypes = $this->getServiceTypes();

        if ($status) {
            $weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->config->get('shipping_novaposhta_weight_class_id'));

            $length = 0;
            $width = 0;
            $height = 0;

            if ($address['iso_code_2'] == 'UA') {

                foreach ($this->cart->getProducts() as $product) {
                    if ($product['height'] > $height) {
                        $height = $product['height'];
                    }

                    if ($product['width'] > $width) {
                        $width = $product['width'];
                    }

                    $length += ($product['length'] * $product['quantity']);
                }

                $curl = curl_init();

                curl_setopt($curl, CURLOPT_HTTPHEADER, array('content-type: application/json'));
                curl_setopt($curl, CURLOPT_URL, 'https://api.novaposhta.ua/v2.0/json/');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
                    'apiKey' => $api_key,
                    'modelName' => 'InternetDocument',
                    'calledMethod' => 'getDocumentPrice',
                    'methodProperties' => array(
                        'CitySender' => '8d5a980d-391c-11dd-90d9-001a92567626',
                        'CityRecipient' => 'db5c88e0-391c-11dd-90d9-001a92567626',
                        'Weight' => urlencode($weight)
                    ),
                    'OptionsSeat' => array(
                        'weight' => urlencode($weight),
                        'volumetricWidth' => $width,
                        'volumetricLength' => $height,
                        'volumetricHeight' => $height
                    ),
                )));
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

                $response = curl_exec($curl);

                curl_close($curl);

                if ($response) {
                    $response_info = array();

                    $response_parts = json_decode($response, true);

                    if (!empty($response_parts['errors'])) {
                        $error = $response_parts['errors'][0];
                    } else {
                        $response_services = $response_parts['data'][0];

                        foreach ($response_services as $response_service['title'] => $response_service['price']) {
                            $response_service['title'] = str_replace('Cost', '', $response_service['title']);

                            if (!empty($response_service['name'] = @$allServicesTypes[$response_service['title']])) {
                                if (!empty($this->settings['general']['service_types'][$response_service['title']])) {
                                    $quote_data[$response_service['title']] = array(
                                        'code'         => 'novaposhta.' .  $response_service['title'],
                                        'title'        => $response_service['name'],
                                        'cost'         => $this->currency->convert($response_service['price'], 'UAH', $this->config->get('config_currency')),
                                        'tax_class_id' => $this->config->get('shipping_novaposhta_tax_class_id'),
                                        'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($response_service['price'], 'UAH', $this->session->data['currency']), $this->config->get('shipping_novaposhta_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1.0000000)
                                    );
                                }
                            }
                        }
                    }
                }
            } else {
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_HTTPHEADER, array('content-type: application/json'));
                curl_setopt($curl, CURLOPT_URL, 'https://api.novaposhta.ua/v2.0/json/');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
                    'apiKey' => $api_key,
                    'modelName' => 'InternetDocument',
                    'calledMethod' => 'getDocumentPrice',
                    'methodProperties' => array(
                        'CitySender' => '8d5a980d-391c-11dd-90d9-001a92567626',
                        'CityRecipient' => 'db5c88e0-391c-11dd-90d9-001a92567626',
                        'Weight' => urlencode($weight)
                    ),
                    'OptionsSeat' => array(
                        'weight' => urlencode($weight),
                        'volumetricWidth' => $width,
                        'volumetricLength' => $height,
                        'volumetricHeight' => $height
                    ),
                )));
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

                $response = curl_exec($curl);

                curl_close($curl);

                if ($response) {
                    $response_info = array();

                    $response_parts = json_decode($response, true);

                    if (!empty($response_parts['errors'])) {
                        $error = $response_parts['errors'][0];
                    } else {
                        $response_services = $response_parts['data'][0];

                        foreach ($response_services as $response_service['title'] => $response_service['price']) {
                            $response_service['title'] = str_replace('Cost', '', $response_service['title']);

                            if (!empty($this->settings['general']['service_types'][$response_service['title']])) {
                                if (!empty($response_service['name'] = @$allServicesTypes[$response_service['title']])) {
                                    $quote_data[$response_service['title']] = array(
                                        'code'         => 'novaposhta.' .  $response_service['title'],
                                        'title'        => $response_service['name'],
                                        'cost'         => $this->currency->convert($response_service['price'], 'UAH', $this->config->get('config_currency')),
                                        'tax_class_id' => $this->config->get('shipping_novaposhta_tax_class_id'),
                                        'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($response_service['price'], 'UAH', $this->session->data['currency']), $this->config->get('shipping_novaposhta_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1.0000000)
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        $method_data = array();

        if ($quote_data) {
            $method_data = array(
                'code'       => 'novaposhta',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('shipping_novaposhta_sort_order'),
                'error'      => $error
            );
        }

        return $method_data;
    }

    public function getCities()
    {
        $result = array();
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTPHEADER, array('content-type: application/json'));
        curl_setopt($curl, CURLOPT_URL, 'https://api.novaposhta.ua/v2.0/json/');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
            'apiKey' => $this->config->get('shipping_novaposhta_api'),
            'modelName' => 'Address',
            'calledMethod' => 'getCities'
        )));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = json_decode(curl_exec($curl), true);

        curl_close($curl);

        if (!empty($response) && $response['success']) {
            foreach ($response['data'] as $eachService) {
                $result[$eachService['Ref']] = $eachService['SettlementTypeDescription'] . ' ' . $eachService['Description'];
            }
        }

        return $result;
    }

    public function searchSettlements($cityName)
    {
        $result = array();
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTPHEADER, array('content-type: application/json'));
        curl_setopt($curl, CURLOPT_URL, 'https://api.novaposhta.ua/v2.0/json/');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
            'apiKey' => $this->config->get('shipping_novaposhta_api'),
            'modelName' => 'Address',
            'calledMethod' => 'searchSettlements',
            'methodProperties' => array(
                'CityName' => $cityName,
                'Limit' => 10
            )
        )));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = json_decode(curl_exec($curl), true);

        curl_close($curl);

        if (!empty($response) && $response['success']) {
            $response = !empty($response['data']['Addresses']) ? $response['data']['Addresses'] : $response['data'][0]['Addresses'];
            foreach ($response as $eachService) {
                $text = $eachService['SettlementTypeCode'] . ' ' . $eachService['MainDescription'] . (!empty($eachService['Region']) ? ' (' . $eachService['Region'] . ' р-н.)' : '');
                $result[] = array(
                    'id' => $text,
                    'ref' => $eachService['Ref'],
                    'text' => $text
                );
            }
        }

        return $result;
    }

    public function getWarehouses($settlementRef)
    {
        $result = array();
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTPHEADER, array('content-type: application/json'));
        curl_setopt($curl, CURLOPT_URL, 'https://api.novaposhta.ua/v2.0/json/');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
            'apiKey' => $this->config->get('shipping_novaposhta_api'),
            'modelName' => 'AddressGeneral',
            'calledMethod' => 'getWarehouses',
            'methodProperties' => array(
                'SettlementRef' => $settlementRef
            )
        )));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = json_decode(curl_exec($curl), true);

        curl_close($curl);

        if (!empty($response) && $response['success']) {
            foreach ($response['data'] as $eachWarehouse) {
                $result[] = array(
                    'id' => $eachWarehouse['Description'],
                    'text' => $eachWarehouse['Description']
                );
            }
        }

        return $result;
    }

    private function getServiceTypes()
    {
        $result = array();
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTPHEADER, array('content-type: application/json'));
        curl_setopt($curl, CURLOPT_URL, 'https://api.novaposhta.ua/v2.0/json/');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
            'apiKey' => $this->config->get('shipping_novaposhta_api'),
            'modelName' => 'Common',
            'calledMethod' => 'getServiceTypes'
        )));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = json_decode(curl_exec($curl), true);

        curl_close($curl);

        if (!empty($response) && $response['success']) {
            foreach ($response['data'] as $eachService) {
                $result[$eachService['Ref']] = $eachService['Description'];
            }
        }

        return $result;
    }

    private function loadSettings()
    {
        $_ = array();
        $configFile = DIR_CONFIG . 'novaposhta.php';

        if (file_exists($configFile)) {
            require $configFile;
            $_ = $_['novaposhta_setting'];
        }
        $this->settings = $_;
    }
}
