<?php
$installer = $this;

$installer->startSetup();
$storesData = $installer->getConnection()->fetchAll("
    SELECT
        DISTINCT (s.website_id)
    FROM
        {$installer->getTable('core/store')} as s,
        {$this->getTable('mondialrelay_pointsrelais')} as mr
    WHERE
    	s.website_id NOT IN (SELECT DISTINCT (website_id) FROM {$this->getTable('mondialrelay_pointsrelais')})
");
        
    foreach ($storesData as $storeData) {
		$websiteId = $storeData['website_id'];
		$query = "INSERT INTO {$this->getTable('mondialrelay_pointsrelais')} (`website_id`, `dest_country_id`, `dest_region_id`, `dest_zip`, `condition_name`, `condition_value`, `price`, `cost`) VALUES
			({$websiteId}, 'FR', 0, '', 'package_weight', 0.5000, 4.2000, 4.2000),
			({$websiteId}, 'FR', 0, '', 'package_weight', 1.0000, 4.2000, 4.2000),
			({$websiteId}, 'FR', 0, '', 'package_weight', 2.0000, 5.5000, 5.5000),
			({$websiteId}, 'FR', 0, '', 'package_weight', 3.0000, 6.2000, 6.2000),
			({$websiteId}, 'FR', 0, '', 'package_weight', 5.0000, 7.5000, 7.5000),
			({$websiteId}, 'FR', 0, '', 'package_weight', 7.0000, 9.6000, 9.6000),
			({$websiteId}, 'FR', 0, '', 'package_weight', 10.0000, 11.9500, 11.9500),
			({$websiteId}, 'FR', 0, '', 'package_weight', 15.0000, 14.3500, 14.3500),
			({$websiteId}, 'FR', 0, '', 'package_weight', 20.0000, 17.9500, 17.9500),
			({$websiteId}, 'BE', 0, '', 'package_weight', 0.5000, 4.2000, 4.2000),
			({$websiteId}, 'BE', 0, '', 'package_weight', 1.0000, 4.8000, 4.8000),
			({$websiteId}, 'BE', 0, '', 'package_weight', 2.0000, 5.5000, 5.5000),
			({$websiteId}, 'BE', 0, '', 'package_weight', 3.0000, 6.2000, 6.2000),
			({$websiteId}, 'BE', 0, '', 'package_weight', 5.0000, 7.5000, 7.5000),
			({$websiteId}, 'BE', 0, '', 'package_weight', 7.0000, 9.6000, 9.6000),
			({$websiteId}, 'BE', 0, '', 'package_weight', 10.0000, 11.9500, 11.9500),
			({$websiteId}, 'BE', 0, '', 'package_weight', 15.0000, 14.3500, 14.3500),
			({$websiteId}, 'BE', 0, '', 'package_weight', 20.0000, 17.9500, 17.9500);
			";
		$installer->run($query);
	}

$installer->endSetup();
