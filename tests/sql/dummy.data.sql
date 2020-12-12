INSERT IGNORE INTO `fb_devices` (`device_id`, `parent_id`, `device_identifier`, `device_name`, `device_comment`, `device_state`, `device_enabled`, `params`, `created_at`, `updated_at`, `device_type`, `owner`) VALUES
(_binary 0x69786D15FD0C4D9F937833287C2009FA, NULL, 'first-device', 'First device', NULL, 'init', 1, '[]', '2020-03-19 14:03:48', '2020-03-22 20:12:07', 'network', '455354e8-96bd-4c29-84e7-9f10e1d4db4b'),
(_binary 0xBF4CD8702AAC45F0A85EE1CEFD2D6D9A, NULL, 'second-device', 'Second device', NULL, 'init', 1, '[]', '2020-03-20 21:54:32', '2020-03-20 21:54:32', 'network', '455354e8-96bd-4c29-84e7-9f10e1d4db4b');

INSERT IGNORE INTO `fb_network_physicals_devices` (`device_id`, `hardware_id`, `firmware_id`) VALUES
(_binary 0x69786D15FD0C4D9F937833287C2009FA, null, null),
(_binary 0xBF4CD8702AAC45F0A85EE1CEFD2D6D9A, null, null);

INSERT IGNORE INTO `fb_physicals_devices_credentials` (`credentials_id`, `device_id`, `credentials_username`, `credentials_password`, `created_at`, `updated_at`) VALUES
(_binary 0x7C055B2B60C3401793DBE9478D8AA662, _binary 0x69786D15FD0C4D9F937833287C2009FA, 'deviceUsername', 'superSecretPassword', '2020-03-20 09:18:20', '2020-03-20 09:18:20');

INSERT IGNORE INTO `vmq_auth_acl` (`account_id`, `device_id`, `mountpoint`, `client_id`, `username`, `password`, `publish_acl`, `subscribe_acl`) VALUES
(_binary 0x1A46CF0ACCE94AB58B7575E2965F2957, binary 0x69786D15FD0C4D9F937833287C2009FA, '', '', 'oldDeviceUsername', 'adc471fdc75ccdc1cc5c7e3a9fb3ff704d40b0cf72c71fe832ba266a5ecff236', '[{"pattern":"/fb/newDeviceUsername/#"}]', '[{"pattern":"/fb/newDeviceUsername/#"}]');
