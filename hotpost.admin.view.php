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

		// Pre-compute filter URLs per board for each filter set.
		$filter_view = array();
		foreach ($config->filters as $i => $filter)
		{
			$row = new stdClass;
			$row->index = $i;
			$row->display_index = $i + 1;
			$row->filter = $filter;
			$row->urls = array();
			if ($filter->query_param !== '' && count($filter->target_modules))
			{
				$target_set = array_map('intval', $filter->target_modules);
				foreach ($board_list as $b)
				{
					if (in_array(intval($b->module_srl), $target_set, true))
					{
						$entry = new stdClass;
						$entry->mid = $b->mid;
						$entry->browser_title = $b->browser_title;
						$entry->url = HotpostModel::getFilterUrl($filter, $b->mid);
						$row->urls[] = $entry;
					}
				}
			}
			$filter_view[] = $row;
		}

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
		);
		$lang_json = json_encode($js_lang, $json_flags);

		Context::set('config', $config);
		Context::set('board_list', $board_list);
		Context::set('filter_view', $filter_view);
		Context::set('boards_json', $boards_json);
		Context::set('lang_json', $lang_json);
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
