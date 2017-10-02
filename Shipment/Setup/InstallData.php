<?php



namespace Speedex\Shipment\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;


    /**
     * Init
     *
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);


        /**
         * Remove previous attributes
         */
        $attributes = ['voucher_id'];
        foreach ($attributes as $attr_to_remove){
            $salesSetup->removeAttribute(\Magento\Sales\Model\Order::ENTITY,$attr_to_remove);

        }

        /**
         * Add 'voucher_id' attributes for order
         */
        $options = ['type' => 'varchar', 'visible' => false, 'required' => false];
        $salesSetup->addAttribute('order', 'voucher_id', $options);

    }
}