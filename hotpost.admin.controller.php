<?php

/**
 * @file hotpost.admin.controller.php
 * @brief Admin controller for the hotpost (인기글) module.
 */
class HotpostAdminController extends Hotpost
{
	/**
	 * Initialize the admin controller.
	 *
	 * @return void
	 */
	public function init()
	{
	}

	/**
	 * Save the module configuration (list of filter sets).
	 *
	 * @return BaseObject|void
	 */
	public function procHotpostAdminInsertConfig()
	{
		$vars = Context::getRequestVars();
		$raw_filters = is_array($vars->filters ?? null) ? $vars->filters : array();

		$filters = array();
		$seen_params = array();
		foreach ($raw_filters as $rf)
		{
			if (!is_array($rf) && !is_object($rf))
			{
				continue;
			}
			$rf = (array) $rf;

			$f = new stdClass;
			$f->name = trim(strval($rf['name'] ?? ''));
			$f->query_param = preg_replace('/[^a-zA-Z0-9_]/', '', strval($rf['query_param'] ?? ''));
			$f->min_readed_count = max(0, intval($rf['min_readed_count'] ?? 0));
			$f->min_voted_count = max(0, intval($rf['min_voted_count'] ?? 0));
			$f->min_comment_count = max(0, intval($rf['min_comment_count'] ?? 0));
			$f->combine_mode = (($rf['combine_mode'] ?? '') === 'or') ? 'or' : 'and';
			$f->period_days = max(0, intval($rf['period_days'] ?? 0));

			$tm = $rf['target_modules'] ?? array();
			if (!is_array($tm))
			{
				$tm = $tm ? array($tm) : array();
			}
			$f->target_modules = array_values(array_unique(array_map('intval', $tm)));

			// Skip completely empty rows.
			if ($f->name === '' && $f->query_param === '' && !count($f->target_modules))
			{
				continue;
			}

			// Drop duplicates by query_param (first wins).
			if ($f->query_param !== '')
			{
				if (isset($seen_params[$f->query_param]))
				{
					continue;
				}
				$seen_params[$f->query_param] = true;
			}

			$filters[] = $f;
		}

		$config = new stdClass;
		$config->filters = $filters;

		$oModuleController = getController('module');
		$output = $oModuleController->insertModuleConfig('hotpost', $config);
		if (!$output->toBool())
		{
			return $output;
		}

		$this->setMessage('success_registed');
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispHotpostAdminConfig'));
	}
}
