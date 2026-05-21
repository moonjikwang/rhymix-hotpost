<?php

/**
 * @file hotpost.class.php
 * @brief Base class for the hotpost (인기글) module.
 */
class Hotpost extends ModuleObject
{
	/**
	 * Default period (in days) for a newly added filter set.
	 */
	const DEFAULT_PERIOD_DAYS = 30;

	/**
	 * Recommended maximum period (in days). Going over this — or setting
	 * 0 (unlimited) — triggers a performance warning in the admin form.
	 */
	const RECOMMENDED_MAX_PERIOD_DAYS = 30;

	/**
	 * Default configuration values.
	 *
	 * @var array
	 */
	protected $_default_config = array(
		'filters' => array(),
	);

	/**
	 * Install the module.
	 *
	 * @return BaseObject|void
	 */
	public function moduleInstall()
	{
		$oModuleController = getController('module');
		$oModuleController->insertModuleConfig('hotpost', (object) $this->_default_config);
	}

	/**
	 * Check whether the module needs to be updated.
	 *
	 * @return bool
	 */
	public function checkUpdate()
	{
		if (!is_object(ModuleModel::getModuleConfig('hotpost')))
		{
			return true;
		}
		return false;
	}

	/**
	 * Update the module.
	 *
	 * @return BaseObject|void
	 */
	public function moduleUpdate()
	{
		$config = ModuleModel::getModuleConfig('hotpost');
		if (!is_object($config))
		{
			$oModuleController = getController('module');
			$oModuleController->insertModuleConfig('hotpost', (object) $this->_default_config);
		}
	}

	/**
	 * Uninstall the module.
	 *
	 * @return BaseObject|void
	 */
	public function moduleUninstall()
	{
	}
}
