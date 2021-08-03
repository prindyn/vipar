<?php

class ControllerExtensionShippingNovaPoshta extends Controller
{
    public function settlements()
    {
        $result = array();
        
        if (isset($this->request->get['search'])) {
            $qCity = $this->request->get['search'];
            
            $this->load->model('extension/shipping/novaposhta');
            
            $result = $this->model_extension_shipping_novaposhta->searchSettlements($qCity);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    public function warehouses()
    {
        $result = array();
        
        if (isset($this->request->get['settlementRef'])) {
            $settlementRef = $this->request->get['settlementRef'];
            
            $this->load->model('extension/shipping/novaposhta');
            
            $result = $this->model_extension_shipping_novaposhta->getWarehouses($settlementRef);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }
}
