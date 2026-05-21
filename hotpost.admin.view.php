<?php

/**
 * @file hotpost.admin.view.php
 * @brief Admin view for the hotpost (인기글) module.
 */
class HotpostAdminView extends Hotpost
{
	/**
	 * Initialize the admin view.
	 *
	 * @return void
	 */
	public function init()
	{
		$this->setTemplatePath(sprintf('%stpl/', $this->module_path));
	}

	/**
	 * Display the configuration form.
	 *
	 * @return BaseObject|void
	 */
	public function dispHotpostAdminConfig()
	{
		$config = HotpostModel::getConfig();

		$args = new stdClass;
		$args->module = 'board';
		$mid_list = ModuleModel::getMidList($args, array('module_srl', 'mid', 'browser_title'));
		$board_list = is_array($mid_list) ? array_values($mid_list) : array();

		$warn_max = self::RECOMMENDED_MAX_PERIOD_DAYS;

		// Pre-compute one example URL per filter set (admin only needs to see the pattern).
		$filter_view = array();
		foreach ($config->filters as $i => $filter)
		{
			$row = new stdClass;
			$row->index = $i;
			$row->display_index = $i + 1;
			$row->filter = $filter;
			$row->example_url = '';
			$row->target_all = !count($filter->target_modules);
			// Warn when the period is unlimited (0) or longer than recommended.
			$row->warn_period = ($filter->period_days === 0 || $filter->period_days > $warn_max);
			if ($filter->query_param !== '')
			{
				// Build a real full URL with a placeholder mid, then swap the placeholder
				// in for a Korean label. The URL respects the site's rewrite settings.
				$placeholder = '__BOARDMID__';
				$full = getNotEncodedFullUrl('', 'mid', $placeholder, $filter->query_param, 'Y');
				$row->example_url = str_replace($placeholder, '<게시판mid>', $full);
			}
			$filter_view[] = $row;
		}

		$period_warning = sprintf($this->_l('hotpost_period_warning'), $warn_max);

		// Build JSON payload used by the add-filter JS template.
		$js_boards = array();
		foreach ($board_list as $b)
		{
			$js_boards[] = array(
				'module_srl' => intval($b->module_srl),
				'mid' => $b->mid,
				'browser_title' => $b->browser_title,
			);
		}
		$json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
		$boards_json = json_encode($js_boards, $json_flags);
		$js_lang = array(
			'filter' => $this->_l('hotpost_filter'),
			'remove' => $this->_l('hotpost_remove_filter'),
			'name' => $this->_l('hotpost_filter_name'),
			'name_placeholder' => $this->_l('hotpost_filter_name_placeholder'),
			'query_param' => $this->_l('hotpost_query_param'),
			'min_readed' => $this->_l('hotpost_min_readed_count'),
			'min_voted' => $this->_l('hotpost_min_voted_count'),
			'min_comment' => $this->_l('hotpost_min_comment_count'),
			'period_days' => $this->_l('hotpost_period_days'),
			'combine_and' => $this->_l('hotpost_combine_mode_and'),
			'combine_or' => $this->_l('hotpost_combine_mode_or'),
			'target_modules' => $this->_l('hotpost_target_modules'),
			'confirm_remove' => $this->_l('hotpost_confirm_remove'),
			'period_warning' => $period_warning,
		);
		$lang_json = json_encode($js_lang, $json_flags);

		Context::set('config', $config);
		Context::set('board_list', $board_list);
		Context::set('filter_view', $filter_view);
		Context::set('boards_json', $boards_json);
		Context::set('lang_json', $lang_json);
		Context::set('period_warning', $period_warning);
		Context::set('period_default', self::DEFAULT_PERIOD_DAYS);
		Context::set('period_warn_max', $warn_max);
		$this->setTemplateFile('config');
	}

	/**
	 * Resolve a lang key to its string in the active language.
	 *
	 * @param string $key
	 * @return string
	 */
	protected function _l(string $key): string
	{
		$value = lang($key);
		return is_string($value) ? $value : $key;
	}
}
