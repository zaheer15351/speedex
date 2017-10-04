<?php

namespace Speedex\Shipment\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MassAction extends \Magento\Ui\Component\MassAction {

    protected $_scopeConfig;

    public function __construct(ContextInterface $context,
                                ScopeConfigInterface $config,
                                $components = [],
                                array $data = []) {
        $this->_scopeConfig = $config;
        parent::__construct($context, $components, $data);
    }


    public function prepare()
    {
        $data =  $this->_scopeConfig->getValue('speedex/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        parent::prepare();


        $config = $this->getConfiguration();

        foreach ($this->getChildComponents() as $actionComponent) {
            $config['actions'][] = $actionComponent->getConfiguration();
        }

        $origConfig = $this->getConfiguration();
        if ($origConfig !== $config) {
            $config = array_replace_recursive($config, $origConfig);
        }
        foreach ($config['actions'] as $key => $action) {
            if(!$data && ($action['type'] == 'create_ship' || $action['type'] == 'cancel_ship')){
                unset($config['actions'][$key]);
            }
        }

        $this->setData('config', $config);
        $this->components = [];

    }

}